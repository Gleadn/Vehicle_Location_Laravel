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
        Schema::table('location_demands', function (Blueprint $table) {
            $table->enum('demand_type', ['generic', 'specific'])->default('generic')->after('user_id');
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('cascade')->after('demand_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('location_demands', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn(['demand_type', 'vehicle_id']);
        });
    }
};
