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
        // Main estimates table
        Schema::create('estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();
            $table->enum('status', ['draft', 'sent', 'approved', 'declined'])->default('draft');
            $table->foreignId('labor_rate_id')->constrained()->onDelete('cascade');
            $table->decimal('markup_percentage', 5, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->date('valid_until')->nullable();
            $table->integer('version')->default(1);
            $table->unsignedInteger('estimate_number');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'estimate_number']);
        });

        // Create sequence table for estimate numbers
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

        // Estimate versions for tracking changes
        Schema::create('estimate_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estimate_id')->constrained()->onDelete('cascade');
            $table->integer('version_number');
            $table->json('snapshot');
            $table->timestamps();
            
            // Each estimate can only have one record per version number
            $table->unique(['estimate_id', 'version_number']);
        });

        // Create estimate packages table for future use
        Schema::create('estimate_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('estimate_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->foreignId('original_package_id')->nullable()->constrained('packages')->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        // Estimate assemblies (assemblies added to estimate)
        Schema::create('estimate_assemblies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('estimate_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assembly_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('original_assembly_id')->nullable();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('original_package_id')->nullable();
            $table->foreignId('estimate_package_id')->nullable()->constrained('estimate_packages')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->timestamps();
            $table->softDeletes();

            // An assembly must belong to either an estimate directly or an estimate package, but not both
            $table->index(['estimate_id', 'estimate_package_id']);
        });

        // Estimate items (direct items added to estimate)
        Schema::create('estimate_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('estimate_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('estimate_assembly_id')->nullable()->constrained('estimate_assemblies')->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('original_item_id')->nullable()->constrained('items')->onDelete('set null');
            $table->unsignedBigInteger('labor_rate_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit_of_measure')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->decimal('material_cost_rate', 10, 2);
            $table->decimal('material_charge_rate', 10, 2);
            $table->decimal('labor_units', 10, 2)->default(0);
            $table->decimal('original_cost_rate', 10, 2);
            $table->decimal('original_charge_rate', 10, 2);
            $table->timestamps();
            $table->softDeletes();
            
            // An item must belong to either an estimate or an estimate assembly, but not both
            $table->index(['estimate_id', 'estimate_assembly_id']);
        });

        // Add foreign key for labor_rate_id if labor_rates table exists
        if (Schema::hasTable('labor_rates')) {
            Schema::table('estimate_items', function (Blueprint $table) {
                $table->foreign('labor_rate_id')->references('id')->on('labor_rates')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign key constraint first if it exists
        if (Schema::hasTable('estimate_items')) {
            Schema::table('estimate_items', function (Blueprint $table) {
                if (Schema::hasTable('labor_rates')) {
                    $table->dropForeign(['labor_rate_id']);
                }
            });
        }

        Schema::dropIfExists('estimate_items');
        Schema::dropIfExists('estimate_assemblies');
        Schema::dropIfExists('estimate_packages');
        Schema::dropIfExists('estimate_versions');
        Schema::dropIfExists('estimates');
        DB::statement('DROP TABLE IF EXISTS tenant_estimate_sequences');
    }
}; 