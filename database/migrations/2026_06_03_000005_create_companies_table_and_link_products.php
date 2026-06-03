<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('company')->constrained()->nullOnDelete();
        });

        Product::query()
            ->select('company')
            ->whereNotNull('company')
            ->distinct()
            ->pluck('company')
            ->filter()
            ->each(function (string $company): void {
                DB::table('companies')->updateOrInsert(
                    ['name' => $company],
                    ['created_at' => now(), 'updated_at' => now()],
                );
            });

        Product::query()->each(function (Product $product): void {
            $companyId = DB::table('companies')->where('name', $product->getAttribute('company'))->value('id');
            $product->forceFill(['company_id' => $companyId])->save();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::dropIfExists('companies');
    }
};
