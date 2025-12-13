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
        Schema::create('price_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Old prices
            $table->decimal('old_cost_price', 10, 2)->nullable();
            $table->decimal('old_retail_price', 10, 2)->nullable();
            $table->decimal('old_wholesale_price', 10, 2)->nullable();
            $table->integer('old_wholesale_min_qty')->nullable();
            
            // New prices
            $table->decimal('new_cost_price', 10, 2)->nullable();
            $table->decimal('new_retail_price', 10, 2)->nullable();
            $table->decimal('new_wholesale_price', 10, 2)->nullable();
            $table->integer('new_wholesale_min_qty')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_adjustments');
    }
};
