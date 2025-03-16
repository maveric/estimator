<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('company_name')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->decimal('default_labor_cost', 10, 4)->default(0);
            $table->decimal('default_labor_rate', 10, 4)->default(0);
            $table->string('currency')->default('USD');
            $table->integer('decimal_places')->default(2);
            $table->string('date_format')->default('Y-m-d');
            $table->boolean('show_cost_in_estimates')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'address',
                'city',
                'state',
                'zip',
                'phone',
                'email',
                'website',
                'default_labor_cost',
                'default_labor_rate',
                'currency',
                'decimal_places',
                'date_format',
                'show_cost_in_estimates',
            ]);
        });
    }
}; 