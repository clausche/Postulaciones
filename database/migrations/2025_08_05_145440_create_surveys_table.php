<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->onDelete('cascade');
            $table->year('ano_encuesta')->default(2023);

            $velocidades = ['Menos de 10', 'Entre 10 y menos de 20', 'entre 20 y menos de 50', 'entre 50 y menos de 100', 'más de 100'];
            $table->enum('velocidad_subida', $velocidades)->nullable();
            $table->enum('velocidad_bajada', $velocidades)->nullable();

            $table->string('computadores_antiguos_2020')->nullable();
            $table->string('proveedor_internet_principal')->nullable(); // Cambiado a string
            $table->year('ultima_compra_ano')->nullable();
            $table->integer('ultima_compra_cantidad')->nullable();
            $table->string('total_computadores')->nullable();

            $table->boolean('sistema_contabilidad_municipal')->nullable();
            $table->boolean('sistema_contabilidad_educacion')->nullable();
            $table->boolean('sistema_contabilidad_salud')->nullable();
            $table->boolean('sistema_adquisiciones')->nullable();
            $table->boolean('sistema_activo_fijo')->nullable();
            $table->boolean('sistema_conciliacion_bancaria')->nullable();
            $table->boolean('sistema_documental')->nullable();

            $table->boolean('programa_doc_digital')->nullable();
            $table->string('propiedad_sistemas')->nullable(); // Cambiado a string

            $table->boolean('gestiona_documentos_plataforma')->nullable();
            $table->boolean('usa_firma_simple')->nullable();
            $table->boolean('usa_firma_avanzada_privada')->nullable();
            $table->boolean('usa_firma_avanzada_segpres')->nullable();
            $table->string('tipo_firma_electronica')->nullable(); // Cambiado a string
            $table->boolean('usa_clave_unica')->nullable();
            $table->boolean('permite_visaciones_plataforma')->nullable();
            $table->boolean('permite_seguimiento_visaciones')->nullable();
            $table->boolean('permite_archivo_electronico')->nullable();
            $table->boolean('plataforma_interaccion_ciudadana')->nullable();

            $table->boolean('tiene_encargado_ti_completo')->nullable();
            $table->boolean('usa_servidores_cloud')->nullable();
            $table->boolean('encargado_conoce_cloud')->nullable();
            $table->boolean('implementado_ley_transformacion_digital')->nullable();

            $table->boolean('sesiones_concejo_streaming')->nullable();
            $table->text('streaming_canal_usuario')->nullable();
            $table->text('streaming_url')->nullable();

            $table->boolean('tiene_facturacion_electronica')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
