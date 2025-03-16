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
        Schema::table('items', function (Blueprint $table) {
            // Remove old labor cost/price columns
            $table->dropColumn(['labor_cost', 'labor_price']);
            
            // Add labor rate reference
            $table->foreignId('labor_rate_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['labor_rate_id']);
            $table->dropColumn('labor_rate_id');
            
            $table->decimal('labor_cost', 10, 4)->nullable();
            $table->decimal('labor_price', 10, 4)->nullable();
        });
    }
};
