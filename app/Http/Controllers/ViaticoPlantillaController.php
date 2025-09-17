<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ViaticoPlantillaController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Viaticos/crear');
    }

    /** Convierte {{campo}} -> ${campo} en document.xml / headers / footers */
    private function normalizeDocxPlaceholders(string $inputDocxPath): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'tpl_') . '.docx';
        if (!@copy($inputDocxPath, $tmp)) {
            throw new \RuntimeException('No se pudo copiar la plantilla DOCX a tmp.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tmp) !== true) {
            throw new \RuntimeException('No se pudo abrir DOCX para normalizar.');
        }

        $targets = ['word/document.xml'];
        // headers/footers también
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#^word/(header|footer)\d+\.xml$#', $name)) {
                $targets[] = $name;
            }
        }

        foreach ($targets as $entry) {
            $xml = $zip->getFromName($entry);
            if ($xml === false) continue;
            // Reemplazo simple {{...}} -> ${...}
            $xml = str_replace(['{{', '}}'], ['${', '}'], $xml);
            $zip->addFromString($entry, $xml);
        }

        $zip->close();
        return $tmp; // ruta del DOCX temporal normalizado
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'sector_salida'  => 'required|string|max:255',
            'motivo_salida'  => 'required|string',
            'fecha_salida'   => 'required|date_format:Y-m-d',
            'hora_salida'    => 'required|date_format:H:i',
            'fecha_llegada'  => 'required|date_format:Y-m-d|after_or_equal:fecha_salida',
            'hora_llegada'   => 'required|date_format:H:i',
        ]);

        $salida  = Carbon::parse($validated['fecha_salida'].' '.$validated['hora_salida']);
        $llegada = Carbon::parse($validated['fecha_llegada'].' '.$validated['hora_llegada']);
        if ($llegada->lt($salida)) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors'  => ['hora_llegada' => ['La hora de regreso debe ser posterior a la de salida.']],
            ], 422);
        }

        try {
            // 1) Ubicar plantilla (app/ primero; si no, app/private del disco local)
            $templatePath = storage_path('app/plantilla_viatico.docx'); // storage/app/
            if (!is_file($templatePath)) {
                $templatePath = Storage::disk('local')->path('plantilla_viatico.docx'); // storage/app/private/
            }
            if (!is_file($templatePath) || !is_readable($templatePath)) {
                return response()->json([
                    'message' => 'No se encontró la plantilla en storage/app (o app/private)/plantilla_viatico.docx',
                ], 404);
            }

            // 2) Normalizar {{}} -> ${} y cargar plantilla
            $normalizedPath = $this->normalizeDocxPlaceholders($templatePath);
            $tp = new TemplateProcessor($normalizedPath);

            // 3) Reemplazos
            $tp->setValue('sector_salida',  $validated['sector_salida']);
            $tp->setValue('motivo_salida',  $validated['motivo_salida']);
            $tp->setValue('fecha_salida',   $salida->format('d-m-Y'));
            $tp->setValue('hora_salida',    $salida->format('H:i'));
            $tp->setValue('fecha_llegada',  $llegada->format('d-m-Y'));
            $tp->setValue('hora_llegada',   $llegada->format('H:i'));
            // Mes y año actual
            $mes_ano = Carbon::now('America/Santiago')->locale('es')->translatedFormat('F Y');
            $tp->setValue('mes_ano', $mes_ano);

            // 4) Guardar final y preparar descarga
            $fileName      = 'Viatico_'.$salida->format('Y-m-d').'.docx';
            $tempRelative  = 'tmp/'.$fileName;
            $tempFullPath  = Storage::disk('local')->path($tempRelative);
            if (!is_dir(dirname($tempFullPath))) {
                mkdir(dirname($tempFullPath), 0775, true);
            }

            $tp->saveAs($tempFullPath);
            $size = @filesize($tempFullPath) ?: null;

            // 5) Descargar y limpiar temporales (final y normalizado)
            return new StreamedResponse(function () use ($tempFullPath, $normalizedPath) {
                $stream = fopen($tempFullPath, 'rb');
                fpassthru($stream);
                fclose($stream);
                @unlink($tempFullPath);
                @unlink($normalizedPath); // limpia el DOCX normalizado
            }, 200, array_filter([
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
                'Content-Length'      => $size ? (string)$size : null,
                'X-Accel-Buffering'   => 'no',
            ]));

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'No se pudo generar el documento.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
