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
            // Adding indexes for frequently searched/filtered columns
            // Using try-catch to prevent errors if index already exists (though migration system usually handles checks)
            try { $table->index('name'); } catch (\Exception $e) {}
            try { $table->index('code'); } catch (\Exception $e) {}
            try { $table->index('category_id'); } catch (\Exception $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            try { $table->dropIndex(['name']); } catch (\Exception $e) {}
            try { $table->dropIndex(['code']); } catch (\Exception $e) {}
            try { $table->dropIndex(['category_id']); } catch (\Exception $e) {}
        });
    }
};
