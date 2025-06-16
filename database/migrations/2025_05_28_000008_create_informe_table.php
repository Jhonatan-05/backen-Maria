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
        Schema::create('informe', function(Blueprint $table) {
            $table->string('codigo')->primary();
            $table->string('idEspecialista');
            $table->string('nombre');
            $table->longText('descripción');
            $table->string('codigoCita');

            $table->foreign('idEspecialista')->references('cedula')->on('especialista');
            $table->foreign('codigoCita')->references('codigo')->on('cita');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informe');
    }
};
