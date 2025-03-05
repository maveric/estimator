<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('assembly_items')) {
            // Check if the assembly_items table has a tenant_id column
            if (!Schema::hasColumn('assembly_items', 'tenant_id')) {
                Schema::table('assembly_items', function (Blueprint $table) {
                    $table->foreignId('tenant_id')->nullable()->after('id');
                    
                    // Add foreign key if tenants table exists
                    if (Schema::hasTable('tenants')) {
                        $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                    }
                });
                
                // Log this action
                if (Schema::hasTable('migration_logs')) {
                    DB::table('migration_logs')->insert([
                        'message' => "Added tenant_id column to assembly_items table",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('assembly_items')) {
            if (Schema::hasColumn('assembly_items', 'tenant_id')) {
                Schema::table('assembly_items', function (Blueprint $table) {
                    // Drop foreign key constraint first if it exists
                    if (Schema::hasTable('tenants')) {
                        $table->dropForeign(['tenant_id']);
                    }
                    
                    $table->dropColumn('tenant_id');
                });
            }
        }
    }
}; 