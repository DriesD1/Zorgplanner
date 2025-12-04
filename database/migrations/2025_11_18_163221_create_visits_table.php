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
    Schema::create('visits', function (Blueprint $table) {
        $table->id();
        $table->foreignId('client_id')->constrained()->cascadeOnDelete();
        
        $table->date('date'); // Wanneer?
        $table->text('notes')->nullable(); // Wat is er gedaan/gezegd?
        
        // Status
        $table->boolean('is_planned')->default(true); // Is het een spook-kruisje?
        $table->boolean('is_done')->default(false); // Is het afgevinkt?
        $table->boolean('is_reported')->default(false); // Is het al gemaild?
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
