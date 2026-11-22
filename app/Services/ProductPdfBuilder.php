<?php

namespace App\Services;

use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Process\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;
use Symfony\Component\Process\PhpExecutableFinder;
use Throwable;

/**
 * Builds the dashboard "Product Price List" PDF.
 *
 * - The finished PDF is cached on disk, keyed by a hash of exactly the data it
 *   shows, so repeat downloads are instant and any price/name/discount change
 *   produces a fresh file.
 * - Large lists are split into page-aligned chunks rendered by parallel
 *   `php artisan products:pdf-chunk` processes, then merged with FPDI, which
 *   also stamps "Page X of Y" on every page.
 * - Progress is published per page so the dashboard can show a real progress bar.
 */
class ProductPdfBuilder
{
    /** Bump when the PDF layout changes so old cached files are not reused. */
    private const VERSION = 3;

    public const FIRST_PAGE_ROWS = 22;

    public const ROWS_PER_PAGE = 28;

    private const MAX_WORKERS = 4;

    private const MIN_PAGES_PER_WORKER = 3;

    private const PROGRESS_TTL = 600;

    /**
     * Returns the path of a ready PDF for these filters, building it if needed.
     */
    public function pdfPath(array $filters, ?string $progressToken = null): string
    {
        $filters = $this->normalizeFilters($filters);
        $rows = $this->rows($filters);
        $key = sha1(json_encode([self::VERSION, @filemtime(resource_path('views/products/pdf.blade.php')), $filters, $rows]));
        $path = $this->cacheDir().'/products-'.$key.'.pdf';

        if (is_file($path)) {
            $this->setProgress($progressToken, ['stage' => 'ready']);

            return $path;
        }

        $this->setProgress($progressToken, ['stage' => 'queued']);

        // If the same PDF is already being built by another request, wait for it.
        return Cache::lock('product-pdf-build:'.$key, 180)->block(175, function () use ($path, $rows, $filters, $progressToken): string {
            if (! is_file($path)) {
                $this->build($rows, $filters, $path, $progressToken);
                $this->pruneCache($path);
            }

            $this->setProgress($progressToken, ['stage' => 'ready']);

            return $path;
        });
    }

    /**
     * Generation progress for the dashboard: stage + percent (0-100).
     */
    public function progress(string $token): array
    {
        $state = $this->progressStore()->get($this->progressKey($token));

        if (! is_array($state)) {
            return ['stage' => 'waiting', 'percent' => 0];
        }

        if (in_array($state['stage'], ['ready', 'merging'], true)) {
            return ['stage' => $state['stage'], 'percent' => 100];
        }

        if ($state['stage'] !== 'rendering') {
            return ['stage' => $state['stage'], 'percent' => 0];
        }

        $done = 0;
        foreach (range(0, max(0, $state['chunks'] - 1)) as $chunk) {
            $done += (int) $this->progressStore()->get($this->progressKey($token).':c'.$chunk, 0);
        }

        return [
            'stage' => 'rendering',
            'percent' => (int) min(99, floor($done / max(1, $state['total_pages']) * 100)),
            'pages_done' => min($done, $state['total_pages']),
            'pages_total' => $state['total_pages'],
        ];
    }

    /**
     * Entry point for the `products:pdf-chunk` command run by worker processes.
     */
    public function renderChunkJob(string $jobPath): void
    {
        $job = json_decode((string) file_get_contents($jobPath), true, flags: JSON_THROW_ON_ERROR);

        $this->renderChunk($job);
    }

    /**
     * Page plan: one array of rows per page; the first page is shorter because of the letterhead.
     *
     * @return array<int, array<int, array<string, mixed>>>
     */
    public static function paginate(array $rows): array
    {
        if ($rows === []) {
            return [[]];
        }

        return array_merge(
            [array_slice($rows, 0, self::FIRST_PAGE_ROWS)],
            array_chunk(array_slice($rows, self::FIRST_PAGE_ROWS), self::ROWS_PER_PAGE),
        );
    }

    private function build(array $rows, array $filters, string $path, ?string $progressToken): void
    {
        $pages = self::paginate($rows);
        $workers = (int) max(1, min(self::MAX_WORKERS, intdiv(count($pages), self::MIN_PAGES_PER_WORKER)));
        $pageGroups = $this->splitEvenly($pages, $workers);
        $workDir = $this->cacheDir().'/tmp-'.Str::random(16);
        File::ensureDirectoryExists($workDir);

        $common = [
            'filters' => $filters,
            'summary' => [
                'products' => count($rows),
                'companies' => collect($rows)->pluck('company')->filter()->unique()->count(),
            ],
            'generated_label' => now()->format('d M Y, h:i A'),
            'date_label' => now()->format('d F Y'),
            'progress_token' => $progressToken,
        ];

        $jobs = [];
        $serialStart = 1;
        foreach ($pageGroups as $index => $groupPages) {
            $jobs[] = $common + [
                'chunk' => $index,
                'show_header' => $index === 0,
                'serial_start' => $serialStart,
                'pages' => $groupPages,
                'output' => $workDir."/chunk-{$index}.pdf",
            ];
            $serialStart += array_sum(array_map('count', $groupPages));
        }

        $this->setProgress($progressToken, ['stage' => 'rendering', 'chunks' => count($jobs), 'total_pages' => count($pages)]);

        try {
            if (count($jobs) === 1 || ! $this->renderInParallel($jobs, $workDir)) {
                // Small lists, or parallel workers unavailable: render in this process.
                foreach ($jobs as $job) {
                    if (! is_file($job['output'])) {
                        $this->renderChunk($job);
                    }
                }
            }

            $this->setProgress($progressToken, ['stage' => 'merging']);
            $this->mergeWithPageNumbers(array_column($jobs, 'output'), $path);
        } finally {
            File::deleteDirectory($workDir);
        }
    }

    private function renderInParallel(array $jobs, string $workDir): bool
    {
        $php = (new PhpExecutableFinder)->find(false);

        if ($php === false) {
            return false;
        }

        try {
            // Compile the Blade template once up front so the workers don't all
            // race to write the same compiled view file.
            $compiler = app('blade.compiler');
            $viewPath = view()->getFinder()->find('products.pdf');
            if ($compiler->isExpired($viewPath)) {
                $compiler->compile($viewPath);
            }

            foreach ($jobs as $job) {
                file_put_contents($workDir."/chunk-{$job['chunk']}.json", json_encode($job, JSON_THROW_ON_ERROR));
            }

            $results = Process::pool(function (Pool $pool) use ($jobs, $php, $workDir): void {
                foreach ($jobs as $job) {
                    $pool->as('chunk-'.$job['chunk'])
                        ->path(base_path())
                        ->timeout(170)
                        ->command([$php, 'artisan', 'products:pdf-chunk', $workDir."/chunk-{$job['chunk']}.json"]);
                }
            })->start()->wait();

            foreach ($jobs as $job) {
                $result = $results['chunk-'.$job['chunk']];

                if ($result->failed() || ! is_file($job['output'])) {
                    Log::warning('Product PDF chunk worker failed; falling back to in-process rendering.', [
                        'chunk' => $job['chunk'],
                        'error' => Str::limit($result->errorOutput() ?: $result->output(), 1000),
                    ]);

                    return false;
                }
            }

            return true;
        } catch (Throwable $e) {
            Log::warning('Product PDF parallel rendering unavailable: '.$e->getMessage());

            return false;
        }
    }

    private function renderChunk(array $job): void
    {
        $pdf = Pdf::loadView('products.pdf', [
            'pages' => $job['pages'],
            'serialStart' => $job['serial_start'],
            'showHeader' => $job['show_header'],
            'summary' => $job['summary'],
            'filters' => $job['filters'],
            'generatedLabel' => $job['generated_label'],
            'dateLabel' => $job['date_label'],
        ])
            ->setPaper('a4')
            ->setOption(['isFontSubsettingEnabled' => true]);

        if ($token = $job['progress_token'] ?? null) {
            $counterKey = $this->progressKey($token).':c'.$job['chunk'];
            $store = $this->progressStore();
            $done = 0;
            $pdf->getDomPDF()->setCallbacks([
                'progress' => [
                    'event' => 'end_page_render',
                    'f' => function () use ($store, $counterKey, &$done): void {
                        $store->put($counterKey, ++$done, self::PROGRESS_TTL);
                    },
                ],
            ]);
        }

        $pdf->save($job['output']);
    }

    /**
     * Joins the chunk PDFs and stamps "Page X of Y" in the footer of every page.
     */
    private function mergeWithPageNumbers(array $files, string $target): void
    {
        $merger = new Fpdi('P', 'pt', 'A4');
        $merger->SetAutoPageBreak(false);
        $merger->SetCreator('Med Bangladesh');
        $merger->SetTitle('Med Bangladesh - Product Price List');

        $pageCounts = array_map(fn (string $file): int => $merger->setSourceFile($file), $files);
        $total = array_sum($pageCounts);
        $number = 0;

        foreach ($files as $i => $file) {
            $merger->setSourceFile($file);

            for ($page = 1; $page <= $pageCounts[$i]; $page++) {
                $template = $merger->importPage($page);
                $size = $merger->getTemplateSize($template);
                $merger->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $merger->useTemplate($template);

                $label = 'Page '.(++$number).' of '.$total;
                $merger->SetFont('Helvetica', '', 6);
                $merger->SetTextColor(138, 152, 147);
                $merger->Text($size['width'] - 27 - $merger->GetStringWidth($label), $size['height'] - 19.7, $label);
            }
        }

        $temp = $target.'.'.Str::random(8).'.tmp';
        $merger->Output('F', $temp);
        rename($temp, $target);
    }

    /**
     * Exactly the data the PDF shows, in display order.
     *
     * @return array<int, array<string, mixed>>
     */
    private function rows(array $filters): array
    {
        return Product::query()
            ->with('companyInfo:id,name')
            ->search($filters['name'] ?? null, $filters['company'] ?? null, $filters['from'] ?? null, $filters['to'] ?? null)
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'company_id', 'company', 'mrp_rate', 'sell_price', 'price', 'discount'])
            ->map(fn (Product $product): array => [
                'name' => $product->name,
                'company' => $product->companyName(),
                'mrp' => $product->mrp_rate === null ? null : (float) $product->mrp_rate,
                'discount' => (int) $product->discount,
                'sell' => $product->effectiveSellPrice(),
            ])
            ->all();
    }

    private function normalizeFilters(array $filters): array
    {
        $filters = array_filter(
            array_map(fn ($value) => is_string($value) ? trim($value) : $value, array_intersect_key($filters, array_flip(['name', 'company', 'from', 'to']))),
            fn ($value) => $value !== null && $value !== '',
        );
        ksort($filters);

        return $filters;
    }

    /**
     * Splits pages into $groups contiguous groups of near-equal size.
     */
    private function splitEvenly(array $pages, int $groups): array
    {
        $result = [];
        $offset = 0;
        $count = count($pages);

        for ($i = 0; $i < $groups; $i++) {
            $size = intdiv($count, $groups) + ($i < $count % $groups ? 1 : 0);
            $result[] = array_slice($pages, $offset, $size);
            $offset += $size;
        }

        return $result;
    }

    private function pruneCache(string $keep): void
    {
        foreach (glob($this->cacheDir().'/products-*.pdf') ?: [] as $file) {
            if ($file !== $keep && @filemtime($file) < now()->subDays(2)->getTimestamp()) {
                @unlink($file);
            }
        }
    }

    private function cacheDir(): string
    {
        $dir = storage_path('app/pdf-cache');
        File::ensureDirectoryExists($dir);

        return $dir;
    }

    private function setProgress(?string $token, array $state): void
    {
        if ($token === null) {
            return;
        }

        $key = $this->progressKey($token);
        $current = $this->progressStore()->get($key, []);
        $this->progressStore()->put($key, array_merge(is_array($current) ? $current : [], $state), self::PROGRESS_TTL);
    }

    private function progressKey(string $token): string
    {
        return 'product-pdf-progress:'.$token;
    }

    private function progressStore(): CacheRepository
    {
        // The file store is shared between the web request and the worker processes.
        return Cache::store('file');
    }
}
