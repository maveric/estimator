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
            $table->foreignId('user_id')->after('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name')->after('user_id');
            $table->text('description')->nullable()->after('name');
            $table->foreignId('labor_rate_id')->after('status')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['labor_rate_id']);
            $table->dropColumn(['user_id', 'name', 'description', 'labor_rate_id']);
        });
    }
}; 