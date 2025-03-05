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
        // Only create the table if it doesn't already exist
        if (!Schema::hasTable('labor_rates')) {
            Schema::create('labor_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->decimal('cost_rate', 10, 2);
                $table->decimal('charge_rate', 10, 2);
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_primary')->default(false);
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
        // Only drop the table if we created it
        if (Schema::hasTable('labor_rates')) {
            Schema::dropIfExists('labor_rates');
        }
    }
}; 