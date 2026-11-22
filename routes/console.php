<?php

use App\Services\ProductPdfBuilder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('products:pdf-chunk {job : Path to the chunk job JSON file}', function (string $job, ProductPdfBuilder $builder) {
    $builder->renderChunkJob($job);
})->purpose('Render one chunk of the product price list PDF (used by parallel PDF generation)');
