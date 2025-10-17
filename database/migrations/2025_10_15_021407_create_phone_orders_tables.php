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
        Schema::create('phone_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->string('status')->default('baru'); // e.g., baru, disiapkan, selesai, dibatalkan
            $table->timestamps();
        });

        Schema::create('phone_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phone_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 10, 3);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_order_items');
        Schema::dropIfExists('phone_orders');
    }
};