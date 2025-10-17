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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('base_unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->foreignId('box_unit_id')->nullable()->constrained('units')->onDelete('set null');

            $table->integer('stock');
            $table->integer('box_stock')->default(0);
            $table->integer('min_stock')->default(10);

            // Harga Jual
            $table->decimal('retail_price', 10, 2); // Harga jual eceran
            $table->decimal('wholesale_price', 10, 2); // Harga jual grosir
            $table->integer('wholesale_min_qty')->default(1);

            // Harga Modal
            $table->decimal('cost_price', 10, 2); // Harga modal per satuan (terakhir)
            
                        // Info Satuan & Box
            
                        $table->decimal('unit_price', 10, 2)->default(0);      // Harga default supplier per satuan
            
                        $table->decimal('box_price', 10, 2)->default(0);       // Harga default supplier per box
            
                        $table->integer('units_in_box')->default(1);        // Jumlah satuan dalam 1 box
            
                        $table->decimal('unit_cost', 10, 2)->default(0); // Modal per satuan (dari PO terakhir)
            
                        $table->decimal('box_cost', 10, 2)->default(0);     // Modal per box (dari PO terakhir)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
