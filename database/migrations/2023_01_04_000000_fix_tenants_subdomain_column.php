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
        if (Schema::hasTable('tenants')) {
            // Check if the tenants table has a subdomain column
            if (!Schema::hasColumn('tenants', 'subdomain')) {
                Schema::table('tenants', function (Blueprint $table) {
                    $table->string('subdomain')->nullable()->after('domain');
                });
                
                // If domain column exists, copy values from domain to subdomain
                if (Schema::hasColumn('tenants', 'domain')) {
                    DB::statement('UPDATE tenants SET subdomain = domain WHERE subdomain IS NULL');
                }
                
                // Log this action
                if (Schema::hasTable('migration_logs')) {
                    DB::table('migration_logs')->insert([
                        'message' => "Added subdomain column to tenants table",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
            
            // Check if the tenants table has a settings column
            if (!Schema::hasColumn('tenants', 'settings')) {
                Schema::table('tenants', function (Blueprint $table) {
                    $table->json('settings')->nullable();
                });
                
                // Log this action
                if (Schema::hasTable('migration_logs')) {
                    DB::table('migration_logs')->insert([
                        'message' => "Added settings column to tenants table",
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
        if (Schema::hasTable('tenants')) {
            if (Schema::hasColumn('tenants', 'subdomain')) {
                Schema::table('tenants', function (Blueprint $table) {
                    $table->dropColumn('subdomain');
                });
            }
            
            if (Schema::hasColumn('tenants', 'settings')) {
                Schema::table('tenants', function (Blueprint $table) {
                    $table->dropColumn('settings');
                });
            }
        }
    }
}; 