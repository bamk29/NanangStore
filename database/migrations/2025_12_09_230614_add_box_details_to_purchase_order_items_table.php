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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->string('unit_type')->default('unit')->after('quantity'); // 'unit' or 'box'
            $table->integer('items_per_box')->default(1)->after('unit_type');
            $table->decimal('box_cost', 15, 2)->default(0)->after('items_per_box');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn(['unit_type', 'items_per_box', 'box_cost']);
        });
    }
};
