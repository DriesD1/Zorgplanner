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
    Schema::create('clients', function (Blueprint $table) {
        $table->id();
        $table->foreignId('house_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->string('room_number')->nullable();
        
        // Planning
        $table->integer('frequency_weeks')->nullable(); // Vb: 6
        $table->date('next_planned_date')->nullable(); // Het ankerpunt voor de matrix
        
        // De Fiche
        $table->json('fiche_data')->nullable(); // De antwoorden op de vragen
        $table->string('feet_drawing_path')->nullable(); // De tekening
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
