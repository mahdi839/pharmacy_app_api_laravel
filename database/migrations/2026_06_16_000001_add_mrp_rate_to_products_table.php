<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('mrp_rate', 10, 2)->nullable()->after('sell_price');
        });

        DB::table('products')->update([
            'mrp_rate' => DB::raw('price'),
            'sell_price' => DB::raw('ROUND(price - (price * discount / 100), 2)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('mrp_rate');
        });
    }
};
