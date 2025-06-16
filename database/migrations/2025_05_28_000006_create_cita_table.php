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
        Schema::create('cita', function (Blueprint $table) {
            $table->string('codigo')->primary();
            $table->string('idCliente');
            $table->string('idRecepcionista')->nullable();
            $table->dateTime('fechaCita');
            $table->string('estado');
            $table->double('costoTotal');

            $table->foreign('idCliente')->references('cedula')->on('cliente')->onDelete('cascade');
            $table->foreign('idRecepcionista')->references('cedula')->on('recepcionista')->onDelete('cascade');
        });

        Schema::create('servicio', function (Blueprint $table) {
            $table->string('codigo')->primary();
            $table->string('nombre');
            $table->longText('descripcion');
            $table->double('precio');
            $table->string('urlImage')->nullable();
            $table->timestamps();
        });

        Schema::create('contieneCita', function (Blueprint $table) {
            $table->string('codigoCita');
            $table->string('codigoServicio');


            $table->foreign('codigoCita')->references('codigo')->on('cita')->onDelete('cascade');
            $table->foreign('codigoServicio')->references('codigo')->on('servicio')->onDelete('cascade');

            $table->primary(['codigoCita', 'codigoServicio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cita');
        Schema::dropIfExists('servicio');
        Schema::dropIfExists('contieneCita');
    }
};
