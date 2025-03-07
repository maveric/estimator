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
        Schema::table('estimates', function (Blueprint $table) {
            $table->decimal('total_cost', 10, 2)->nullable()->after('discount_percentage');
            $table->decimal('total_charge', 10, 2)->nullable()->after('total_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn('total_cost');
            $table->dropColumn('total_charge');
        });
    }
}; 