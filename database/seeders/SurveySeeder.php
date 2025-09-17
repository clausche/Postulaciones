<?php

namespace Database\Seeders;

use App\Models\Survey;
use App\Models\Platform;
use App\Models\Provider;
use App\Models\Municipality;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SurveySeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Survey::truncate();
        Municipality::truncate();
        Provider::truncate();
        Platform::truncate();
        DB::table('provider_survey')->truncate();
        DB::table('platform_survey')->truncate();
        Schema::enableForeignKeyConstraints();

        $csvFile = fopen(database_path("data/Encuesta_Realidad_Tecnologica_2023.csv"), "r");

        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {
            if (empty($data[0]) || !is_numeric($data[0])) {
                continue;
            }

            $cleanValue = function ($value) {
                $nullValues = ['No recepcionado', 'No aplica', ''];
                $trimmedValue = trim($value);
                return in_array($trimmedValue, $nullValues) ? null : $trimmedValue;
            };
            $toBoolean = function ($value) use ($cleanValue) {
                $cleaned = $cleanValue($value);
                return $cleaned === null ? null : strtolower($cleaned) === 'si';
            };
            $parseCompra = function ($value) use ($cleanValue) {
                $cleaned = $cleanValue($value);
                if ($cleaned === null) return ['ano' => null, 'cantidad' => null];
                if (preg_match('/(\d{4})\s*-\s*(\d+)/', $cleaned, $matches)) return ['ano' => (int)$matches[1], 'cantidad' => (int)$matches[2]];
                if (preg_match('/^\d{4}$/', $cleaned)) return ['ano' => (int)$cleaned, 'cantidad' => null];
                if (preg_match('/(\d+)\s*equipo/', $cleaned, $matches)) return ['ano' => null, 'cantidad' => (int)$matches[1]];
                return ['ano' => null, 'cantidad' => null];
            };

            $municipality = Municipality::firstOrCreate(['codigo_municipio' => $data[0]], ['nombre' => $data[1]]);
            $compraData = $parseCompra($data[6]);

            $survey = Survey::create([
                'municipality_id' => $municipality->id, 'ano_encuesta' => 2023,
                'velocidad_subida' => $cleanValue($data[2]), 'velocidad_bajada' => $cleanValue($data[3]),
                'computadores_antiguos_2020' => $cleanValue($data[4]), 'proveedor_internet_principal' => $cleanValue($data[5]),
                'ultima_compra_ano' => $compraData['ano'], 'ultima_compra_cantidad' => $compraData['cantidad'],
                'total_computadores' => $cleanValue($data[7]), 'sistema_contabilidad_municipal' => $toBoolean($data[8]),
                'sistema_contabilidad_educacion' => $toBoolean($data[9]), 'sistema_contabilidad_salud' => $toBoolean($data[10]),
                'sistema_adquisiciones' => $toBoolean($data[11]), 'sistema_activo_fijo' => $toBoolean($data[12]),
                'sistema_conciliacion_bancaria' => $toBoolean($data[13]), 'sistema_documental' => $toBoolean($data[14]),
                'programa_doc_digital' => $toBoolean($data[15]), 'propiedad_sistemas' => $cleanValue($data[17]),
                'gestiona_documentos_plataforma' => $toBoolean($data[19]), 'usa_firma_simple' => $toBoolean($data[20]),
                'usa_firma_avanzada_privada' => $toBoolean($data[21]), 'usa_firma_avanzada_segpres' => $toBoolean($data[22]),
                'tipo_firma_electronica' => $cleanValue($data[24]), 'usa_clave_unica' => $toBoolean($data[25]),
                'permite_visaciones_plataforma' => $toBoolean($data[26]), 'permite_seguimiento_visaciones' => $toBoolean($data[27]),
                'permite_archivo_electronico' => $toBoolean($data[28]), 'plataforma_interaccion_ciudadana' => $toBoolean($data[29]),
                'tiene_encargado_ti_completo' => $toBoolean($data[30]), 'usa_servidores_cloud' => $toBoolean($data[31]),
                'encargado_conoce_cloud' => $toBoolean($data[32]), 'implementado_ley_transformacion_digital' => $toBoolean($data[33]),
                'sesiones_concejo_streaming' => $toBoolean($data[34]), 'streaming_canal_usuario' => $cleanValue($data[36]),
                'streaming_url' => $cleanValue($data[37]), 'tiene_facturacion_electronica' => $toBoolean($data[38]),
            ]);

            if (!empty($data[18])) {
                $providerNames = preg_split('/( - | y |;|,|\n)/', $data[18]);
                foreach ($providerNames as $name) {
                    $trimmed = trim($name);
                    if (!empty($trimmed) && $trimmed !== 'No aplica') {
                        $provider = Provider::firstOrCreate(['nombre' => $trimmed]);
                        $survey->providers()->attach($provider->id);
                    }
                }
            }

            if (!empty($data[35])) {
                $platformNames = preg_split('/( - | y |;|,|\n)/', $data[35]);
                foreach ($platformNames as $name) {
                    $trimmed = trim($name);
                    if (!empty($trimmed) && $trimmed !== 'No aplica') {
                        $finalName = $trimmed;
                        if (stripos($finalName, 'Facebook') !== false) $finalName = 'Facebook';
                        if (stripos($finalName, 'Youtube') !== false) $finalName = 'Youtube';
                        if (stripos($finalName, 'Web municipal') !== false) $finalName = 'Web municipal';
                        if (stripos($finalName, 'Twiter') !== false) $finalName = 'Twitter';
                        $platform = Platform::firstOrCreate(['nombre' => $finalName]);
                        $survey->platforms()->attach($platform->id);
                    }
                }
            }
        }
        fclose($csvFile);
    }
}
