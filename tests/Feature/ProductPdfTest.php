<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Product;
use App\Services\ProductPdfBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use setasign\Fpdi\Fpdi;
use Tests\TestCase;

class ProductPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        File::deleteDirectory(storage_path('app/pdf-cache'));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/pdf-cache'));
        parent::tearDown();
    }

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin',
            'phone' => '01700000000',
            'gmail' => 'admin@example.com',
            'password' => 'password',
        ]);
    }

    private function product(string $name, float $mrp, float $sell, int $discount = 0): Product
    {
        return Product::create([
            'name' => $name,
            'company' => 'Test Pharma',
            'strength' => '500mg',
            'form' => 'Tablet',
            'purchase_price' => 5,
            'price' => $mrp,
            'mrp_rate' => $mrp,
            'sell_price' => $sell,
            'stock' => 10,
            'discount' => $discount,
        ]);
    }

    private function pageCount(string $path): int
    {
        return (new Fpdi)->setSourceFile($path);
    }

    public function test_guest_cannot_download_product_pdf(): void
    {
        $this->get('/products/pdf')->assertRedirect('/admin/login');
        $this->get('/products/pdf/progress/abcdefghijklmnop')->assertRedirect('/admin/login');
    }

    public function test_admin_can_download_product_pdf(): void
    {
        $this->product('Napa Extra', 25, 22);

        $response = $this->actingAs($this->admin())->get('/products/pdf');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', file_get_contents($response->baseResponse->getFile()->getPathname()));
    }

    public function test_pdf_view_shows_brand_company_discount_and_prices(): void
    {
        $html = view('products.pdf', [
            'pages' => [[
                ['name' => 'Napa Extra', 'company' => 'Test Pharma', 'mrp' => 25.0, 'discount' => 12, 'sell' => 22.0],
                ['name' => 'Seclo', 'company' => 'Square', 'mrp' => null, 'discount' => 0, 'sell' => 70.0],
            ]],
            'serialStart' => 1,
            'showHeader' => true,
            'summary' => ['products' => 2, 'companies' => 2],
            'filters' => [],
            'generatedLabel' => '22 Nov 2026, 10:00 AM',
            'dateLabel' => '22 November 2026',
        ])->render();

        $this->assertStringContainsString('Med Bangladesh', $html);
        $this->assertStringContainsString('Napa Extra', $html);
        $this->assertStringContainsString('Test Pharma', $html);
        $this->assertStringContainsString('12% OFF', $html);
        $this->assertStringContainsString('BDT 25.00', $html);
        $this->assertStringContainsString('BDT 22.00', $html);
        $this->assertStringNotContainsString('Stock', $html);
    }

    public function test_pdf_is_cached_until_the_shown_data_changes(): void
    {
        $product = $this->product('Napa Extra', 25, 22);
        $builder = app(ProductPdfBuilder::class);

        $first = $builder->pdfPath([]);
        $this->assertSame($first, $builder->pdfPath([]));

        // Stock is not shown in the PDF, so it must not invalidate the cache.
        $product->update(['stock' => 3]);
        $this->assertSame($first, $builder->pdfPath([]));

        $product->update(['sell_price' => 20]);
        $this->assertNotSame($first, $builder->pdfPath([]));

        // Filters get their own cached file.
        $this->assertNotSame($builder->pdfPath([]), $builder->pdfPath(['name' => 'napa']));
    }

    public function test_large_list_is_rendered_in_parallel_chunks_and_merged(): void
    {
        Log::spy();

        foreach (range(1, 200) as $i) {
            $this->product(sprintf('Medicine %03d', $i), 25, 22, $i % 2 ? 5 : 0);
        }

        $path = app(ProductPdfBuilder::class)->pdfPath([], 'test-token-0123456789');

        // 22 rows on the first page, then 28 per page.
        $this->assertSame(1 + (int) ceil((200 - 22) / 28), $this->pageCount($path));
        Log::shouldNotHaveReceived('warning');

        $this->actingAs($this->admin())
            ->getJson('/products/pdf/progress/test-token-0123456789')
            ->assertOk()
            ->assertJson(['stage' => 'ready', 'percent' => 100]);
    }

    public function test_progress_for_unknown_token_is_waiting(): void
    {
        $this->actingAs($this->admin())
            ->getJson('/products/pdf/progress/unknown-token-0000000')
            ->assertOk()
            ->assertJson(['stage' => 'waiting', 'percent' => 0]);
    }

    public function test_products_page_has_download_pdf_button_with_current_filters(): void
    {
        $this->actingAs($this->admin())
            ->get('/products?name=napa')
            ->assertOk()
            ->assertSee('Download PDF')
            ->assertSee(route('products.pdf', ['name' => 'napa']), false)
            ->assertSee('id="pdf-progress"', false);
    }
}
