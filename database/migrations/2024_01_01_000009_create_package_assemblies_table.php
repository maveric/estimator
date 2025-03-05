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
        // Check if the table already exists
        if (!Schema::hasTable('package_assemblies')) {
            Schema::create('package_assemblies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('package_id')->constrained()->onDelete('cascade');
                $table->foreignId('assembly_id')->constrained()->onDelete('cascade');
                $table->decimal('quantity', 10, 2)->default(1);
                $table->timestamps();
                
                // Removing the unique constraint to allow multiple of the same assembly
                // $table->unique(['package_id', 'assembly_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_assemblies');
    }
}; 