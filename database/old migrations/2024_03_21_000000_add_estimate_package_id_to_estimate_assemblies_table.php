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
        Schema::table('estimate_assemblies', function (Blueprint $table) {
            $table->foreignId('estimate_package_id')->nullable()->after('estimate_id')
                  ->constrained('estimate_packages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estimate_assemblies', function (Blueprint $table) {
            $table->dropForeign(['estimate_package_id']);
            $table->dropColumn('estimate_package_id');
        });
    }
}; 