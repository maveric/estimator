<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->decimal('default_material_markup', 5, 2)->default(1.25);
            $table->decimal('default_labor_markup', 5, 2)->default(1.25);
            $table->decimal('primary_labor_rate', 10, 2)->default(50.00);
            $table->timestamps();
        });

        // Insert the default settings for each tenant
        $tenants = DB::table('tenants')->pluck('id');
        foreach ($tenants as $tenantId) {
            DB::table('settings')->insert([
                'tenant_id' => $tenantId,
                'default_material_markup' => 1.25,
                'default_labor_markup' => 1.25,
                'primary_labor_rate' => 50.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
} 