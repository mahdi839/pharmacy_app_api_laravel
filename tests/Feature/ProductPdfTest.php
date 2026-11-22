<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPdfTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_guest_cannot_download_product_pdf(): void
    {
        $this->get('/products/pdf')->assertRedirect('/admin/login');
    }

    public function test_admin_can_download_product_pdf(): void
    {
        $this->product('Napa Extra', 25, 22);

        $response = $this->actingAs($this->admin())->get('/products/pdf');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_pdf_view_shows_brand_company_discount_and_prices(): void
    {
        $this->product('Napa Extra', 25, 22, 12);

        $html = view('products.pdf', [
            'products' => Product::all(),
            'generatedAt' => now(),
        ])->render();

        $this->assertStringContainsString('Med Bangladesh', $html);
        $this->assertStringContainsString('Napa Extra', $html);
        $this->assertStringContainsString('Test Pharma', $html);
        $this->assertStringContainsString('12% OFF', $html);
        $this->assertStringContainsString('BDT 25.00', $html);
        $this->assertStringContainsString('BDT 22.00', $html);
        // Purchase price and stock must not leak into the PDF.
        $this->assertStringNotContainsString('BDT 5.00', $html);
        $this->assertStringNotContainsString('Stock', $html);
    }

    public function test_products_page_has_download_pdf_button_with_current_filters(): void
    {
        $this->actingAs($this->admin())
            ->get('/products?name=napa')
            ->assertOk()
            ->assertSee('Download PDF')
            ->assertSee(route('products.pdf', ['name' => 'napa']), false);
    }
}
