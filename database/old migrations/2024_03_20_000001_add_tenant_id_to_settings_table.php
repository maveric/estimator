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
        Schema::table('settings', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->after('id')->nullable();
        });

        // Update existing records with tenant_id from the first user
        $firstUser = DB::table('users')->first();
        if ($firstUser) {
            DB::table('settings')->update(['tenant_id' => $firstUser->tenant_id]);
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->dropColumn('is_primary'); // Remove the is_primary column as it's not needed in settings
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
            $table->boolean('is_primary')->default(false);
        });
    }
}; 