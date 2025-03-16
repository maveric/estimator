<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku')->unique();
            $table->string('unit_of_measure')->nullable();
            // Material costs
            $table->decimal('material_cost', 10, 4)->default(0);
            $table->decimal('material_price', 10, 4)->default(0);
            // Labor time and rates
            $table->integer('labor_minutes')->default(0);
            $table->decimal('labor_cost', 10, 4)->default(0);
            $table->decimal('labor_price', 10, 4)->default(0);
            // Item type and status
            $table->enum('type', ['material', 'labor', 'both'])->default('material');
            $table->boolean('is_template')->default(false);
            $table->boolean('is_active')->default(true);
            // Timestamps and soft deletes
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['team_id', 'type']);
            $table->index(['team_id', 'is_template']);
            $table->unique(['team_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
}; 