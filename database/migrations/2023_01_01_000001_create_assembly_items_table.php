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
        // Create the assemblies table if it doesn't exist yet
        if (!Schema::hasTable('assemblies')) {
            Schema::create('assemblies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
            
            // Add foreign key constraint separately if tenants table exists
            if (Schema::hasTable('tenants')) {
                Schema::table('assemblies', function (Blueprint $table) {
                    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                });
            }
        }
        
        // Create the items table if it doesn't exist yet
        if (!Schema::hasTable('items')) {
            Schema::create('items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable();
                $table->string('name');
                $table->string('sku')->nullable();
                $table->text('description')->nullable();
                $table->decimal('cost', 10, 2)->default(0);
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('material_cost', 10, 2)->default(0);
                $table->decimal('material_cost_rate', 10, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
            
            // Add foreign key constraint separately if tenants table exists
            if (Schema::hasTable('tenants')) {
                Schema::table('items', function (Blueprint $table) {
                    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                });
            }
        } else {
            // If items table exists but doesn't have the material_cost or material_cost_rate columns, add them
            if (!Schema::hasColumn('items', 'material_cost')) {
                Schema::table('items', function (Blueprint $table) {
                    $table->decimal('material_cost', 10, 2)->default(0);
                });
            }
            
            if (!Schema::hasColumn('items', 'material_cost_rate')) {
                Schema::table('items', function (Blueprint $table) {
                    $table->decimal('material_cost_rate', 10, 2)->default(0);
                });
            }
        }
        
        // Now create the assembly_items table
        if (!Schema::hasTable('assembly_items')) {
            Schema::create('assembly_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('assembly_id');
                $table->unsignedBigInteger('item_id');
                $table->integer('quantity')->default(1);
                $table->timestamps();
                
                // Add foreign keys
                $table->foreign('assembly_id')->references('id')->on('assemblies')->onDelete('cascade');
                $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
                
                // Prevent duplicate items in the same assembly
                $table->unique(['assembly_id', 'item_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assembly_items');
    }
}; 