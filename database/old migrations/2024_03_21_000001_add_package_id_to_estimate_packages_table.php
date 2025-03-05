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
        Schema::table('estimate_packages', function (Blueprint $table) {
            $table->foreignId('package_id')->after('estimate_id')->constrained()->onDelete('cascade');
            $table->foreignId('original_package_id')->after('package_id')->nullable()->constrained('packages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estimate_packages', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropForeign(['original_package_id']);
            $table->dropColumn(['package_id', 'original_package_id']);
        });
    }
}; 