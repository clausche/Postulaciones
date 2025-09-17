<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viaticos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->string('rut', 15);
            $table->string('escalafon', 50)->nullable();
            $table->string('grado', 20)->nullable();
            $table->string('funcion', 150)->nullable();

            $table->string('lugar', 200);
            $table->string('motivo', 500);

            $table->date('dia_salida');
            $table->time('hora_salida');
            $table->date('dia_regreso');
            $table->time('hora_regreso');

            $table->string('vehiculo', 10)->default('M05'); // M04..M99
            $table->string('patente', 20)->nullable();

            $table->string('resolucion', 120)->nullable();

            // por si quieres almacenar el path del docx generado
            $table->string('docx_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viaticos');
    }
};
