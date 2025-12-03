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
        Schema::table('transactions', function (Blueprint $table) {
            try {
                $table->index('customer_id');
            } catch (\Exception $e) {}
            
            try {
                $table->index('created_at');
            } catch (\Exception $e) {}
            
            try {
                $table->index('status');
            } catch (\Exception $e) {}
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            try {
                $table->index('product_id');
            } catch (\Exception $e) {}
            
            try {
                $table->index('transaction_id');
            } catch (\Exception $e) {}
        });

        Schema::table('product_usages', function (Blueprint $table) {
            try {
                $table->index('usage_count');
            } catch (\Exception $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            try { $table->dropIndex(['customer_id']); } catch (\Exception $e) {}
            try { $table->dropIndex(['created_at']); } catch (\Exception $e) {}
            try { $table->dropIndex(['status']); } catch (\Exception $e) {}
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            try { $table->dropIndex(['product_id']); } catch (\Exception $e) {}
            try { $table->dropIndex(['transaction_id']); } catch (\Exception $e) {}
        });

        Schema::table('product_usages', function (Blueprint $table) {
            try { $table->dropIndex(['usage_count']); } catch (\Exception $e) {}
        });
    }
};
