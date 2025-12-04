<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // 'string' = max 255 tekens (te klein voor plaatjes).
            // 'text' = max 64.000 tekens (soms nog te klein).
            // 'longText' = max 4 MILJARD tekens (groot genoeg voor elke tekening!).
            $table->longText('feet_drawing_path')->change();
        });
    }

    
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Als we terugdraaien, maken we er weer een simpele 'string' van.
            // Let op: hierdoor zou je je tekeningen kwijtraken omdat ze worden afgekapt!
            $table->string('feet_drawing_path')->change();
        });
    }
};