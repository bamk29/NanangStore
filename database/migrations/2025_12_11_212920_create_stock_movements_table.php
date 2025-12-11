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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['sale', 'purchase', 'adjustment', 'return', 'item_add', 'item_remove', 'correction']); // 'item_add/remove' generic for manual edits
            $table->decimal('quantity', 15, 2); // Can be negative for output
            $table->decimal('stock_before', 15, 2);
            $table->decimal('stock_after', 15, 2);
            $table->string('reference_id')->nullable(); // Transaction ID or PO ID
            $table->string('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Who did it
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
