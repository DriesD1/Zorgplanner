<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id'); // Wie?
            $table->foreignId('house_id');  // Welk huis?
            $table->integer('year');        // Welk jaar?
            $table->integer('week_number'); // Welke week?
            $table->date('date')->nullable(); // De datum die jij invult
            $table->text('note')->nullable(); // De tekst die jij schrijft
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_entries');
    }
};