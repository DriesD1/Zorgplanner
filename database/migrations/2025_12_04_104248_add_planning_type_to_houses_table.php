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
        Schema::table('houses', function (Blueprint $table) {
            // We maken hem nullable, want als 'has_custom_schedule' uit staat, boeit dit niet.
            // Opties worden: 'week' of 'day'
            $table->string('planning_type')->nullable()->default('week')->after('has_custom_schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            //
        });
    }
};
