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
        // Verander van 'string' (kort) naar 'longText' (gigantisch)
        $table->longText('feet_drawing_path')->change()->nullable();
    });
}

public function down(): void
{
    Schema::table('clients', function (Blueprint $table) {
        // Terug naar string (als we ooit terug willen, wat niet waarschijnlijk is)
        // Let op: dit zou data kunnen afkappen, dus wees voorzichtig met down()
        $table->string('feet_drawing_path')->change();
    });
}
};
