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
        Schema::table('estimates', function (Blueprint $table) {
            $table->unsignedInteger('estimate_number')->after('tenant_id')->nullable();
        });

        // Create a sequence table for each tenant
        DB::statement('
            CREATE TABLE tenant_estimate_sequences (
                tenant_id bigint unsigned NOT NULL,
                last_estimate_number int unsigned NOT NULL DEFAULT 0,
                PRIMARY KEY (tenant_id),
                FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
            )
        ');

        // Initialize sequences for existing tenants
        $tenants = DB::table('tenants')->pluck('id');
        foreach ($tenants as $tenantId) {
            DB::table('tenant_estimate_sequences')->insert([
                'tenant_id' => $tenantId,
                'last_estimate_number' => 0
            ]);
        }

        // Update existing estimates with sequential numbers per tenant
        $estimates = DB::table('estimates')
            ->orderBy('tenant_id')
            ->orderBy('created_at')
            ->get();

        foreach ($estimates as $estimate) {
            DB::table('tenant_estimate_sequences')
                ->where('tenant_id', $estimate->tenant_id)
                ->increment('last_estimate_number');

            DB::table('estimates')
                ->where('id', $estimate->id)
                ->update([
                    'estimate_number' => DB::table('tenant_estimate_sequences')
                        ->where('tenant_id', $estimate->tenant_id)
                        ->value('last_estimate_number')
                ]);
        }

        // Make estimate_number not nullable after populating
        Schema::table('estimates', function (Blueprint $table) {
            $table->unsignedInteger('estimate_number')->nullable(false)->change();
        });

        // Add unique constraint for tenant_id + estimate_number
        Schema::table('estimates', function (Blueprint $table) {
            $table->unique(['tenant_id', 'estimate_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'estimate_number']);
            $table->dropColumn('estimate_number');
        });

        DB::statement('DROP TABLE tenant_estimate_sequences');
    }
}; 