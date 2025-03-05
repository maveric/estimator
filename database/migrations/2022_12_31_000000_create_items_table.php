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
        if (!Schema::hasTable('items')) {
            Schema::create('items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('sku')->nullable();
                $table->text('description')->nullable();
                $table->decimal('cost', 10, 2)->default(0);
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('material_cost', 10, 2)->default(0);
                $table->decimal('material_cost_rate', 10, 2)->default(0);
                $table->decimal('material_charge_rate', 10, 2)->default(0);
                $table->decimal('labor_units', 10, 2)->default(0);
                $table->string('unit_of_measure')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
}; 