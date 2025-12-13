<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
             // Index key length 50 is sufficient for searching abbreviations
             // Using DB::raw for custom index length if needed, or Schema if supported
             try {
                $table->index([\Illuminate\Support\Facades\DB::raw('description(50)')], 'products_description_index');
             } catch (\Exception $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            try { $table->dropIndex('products_description_index'); } catch (\Exception $e) {}
        });
    }
};
