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
        Schema::table('clients', function (Blueprint $table) {
            // Hier zeggen we: het mag ook leeg (null) zijn
            $table->longText('feet_drawing_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Terugdraaien naar verplicht (als het moet)
            $table->longText('feet_drawing_path')->nullable(false)->change();
        });
    }
};