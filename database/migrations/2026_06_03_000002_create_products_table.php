<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('company', 120);
            $table->string('strength', 50);
            $table->string('form', 50);
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedTinyInteger('discount')->default(0);
            $table->string('image', 500)->nullable();
            $table->timestamps();

            $table->index(['name', 'company']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
