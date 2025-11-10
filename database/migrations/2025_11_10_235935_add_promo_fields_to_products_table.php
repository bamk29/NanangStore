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
            $table->string('abbr')->nullable()->after('name');
            $table->boolean('promo_isactive')->default(false)->after('abbr');
            $table->enum('promo_type', ['percentage_discount', 'fixed_discount', 'special_promo'])->nullable()->after('promo_isactive');
            $table->unsignedInteger('promo_min_qty')->nullable()->after('promo_type');
            $table->decimal('promo_value', 15, 2)->nullable()->after('promo_min_qty');
            $table->string('promo_note')->nullable()->after('promo_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['abbr', 'promo_isactive', 'promo_type', 'promo_min_qty', 'promo_value', 'promo_note']);
        });
    }
};