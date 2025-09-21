<?php

namespace App\Http\Controllers;

use App\Models\Viatico;
use App\Rules\RutChileno;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ViaticoController extends Controller
{
    /** Formulario (Inertia) */
    public function create()
    {
        return Inertia::render('Viaticos/create', [
            'vehiculos'       => $this->vehiculos(),
            'defaultVehiculo' => 'M05',
            'csrf_token'      => csrf_token(),
        ]);
    }

    /** Guarda en BD + genera y descarga DOCX */
    public function store(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $data = $request->validate([
            'nombre'          => ['required','string','max:200'],
            'rut'             => ['required','string','max:15', new RutChileno],
            'escalafon'       => ['nullable','string','max:50'],
            'grado'           => ['nullable','string','max:20'],
            'funcion'         => ['nullable','string','max:150'],

            'lugar'           => ['required','string','max:200'],
            'motivo'          => ['required','string','max:500'],

            'dia_salida'      => ['required','date'],
            'hora_salida'     => ['required','date_format:H:i'],
            'dia_regreso'     => ['required','date','after_or_equal:dia_salida'],
            'hora_regreso'    => ['required','date_format:H:i'],

            'vehiculo'        => ['required','string','regex:/^M(0[4-9]|[1-9][0-9])$/'], // M04..M99
            'patente'         => ['nullable','string','max:20'],
            'bitacora_numero' => ['nullable','string','max:50'],

            'resolucion'      => ['nullable','string','max:120'],
        ]);

        try {
            // Checks de dependencias y permisos
            if (!class_exists(TemplateProcessor::class)) {
                throw new \RuntimeException('Falta phpoffice/phpword (composer require phpoffice/phpword).');
            }
            if (!class_exists(\ZipArchive::class)) {
                throw new \RuntimeException('PHP ext-zip no está habilitada (ZipArchive).');
            }

            // Plantilla en storage/app/viatico.docx
            $original = Storage::path('viatico.docx');
            if (!is_file($original)) {
                throw new \RuntimeException('Falta plantilla: ' . $original);
            }

            $viaticosDir = storage_path('app/viaticos');
            if (!is_dir($viaticosDir) && !@mkdir($viaticosDir, 0775, true)) {
                throw new \RuntimeException('No se pudo crear storage/app/viaticos.');
            }
            if (!is_writable($viaticosDir)) {
                throw new \RuntimeException('Sin permisos de escritura en storage/app/viaticos.');
            }

            // Derivados + persistencia
            $data['user_id'] = Auth::id(); // requiere estar autenticado si user_id es NOT NULL
            $data['folio']   = $this->nuevoFolio();
            $data['mes_ano'] = $this->mesAno($data['dia_salida']);

            $viatico = Viatico::create($data);

            // Normaliza {{}} -> ${} y carga
            $tplPath = $this->normalizeDocxPlaceholders($original);
            $tp = new TemplateProcessor($tplPath);

            // Fechas seguras
            $diaSalida  = Carbon::parse($viatico->dia_salida, 'America/Santiago');
            $diaRegreso = Carbon::parse($viatico->dia_regreso, 'America/Santiago');

            // Placeholders
            $tp->setValue('nombre',        $viatico->nombre);
            $tp->setValue('rut',           $this->rutFormateado($viatico->rut));
            $tp->setValue('escalafon',     (string)($viatico->escalafon ?? ''));
            $tp->setValue('grado',         (string)($viatico->grado ?? ''));
            $tp->setValue('funcion',       (string)($viatico->funcion ?? ''));

            $tp->setValue('lugar',         $viatico->lugar);
            $tp->setValue('motivo',        $viatico->motivo);

            $tp->setValue('dia_salida',    $diaSalida->format('d-m-Y'));
            $tp->setValue('hora_salida',   $viatico->hora_salida);
            $tp->setValue('dia_regreso',   $diaRegreso->format('d-m-Y'));
            $tp->setValue('hora_regreso',  $viatico->hora_regreso);

            $tp->setValue('vehiculo',      $viatico->vehiculo ?: 'M05');
            $tp->setValue('patente',       (string)($viatico->patente ?? ''));
            $tp->setValue('resolucion',    (string)($viatico->resolucion ?? ''));
            //$tp->setValue('mes_ano',       $viatico->mes_ano);
            $tp->setValue('mes_ano', $data['mes_ano']);

            // Guardar salida en storage/app/viaticos/
            $filename = 'viatico_' . now('America/Santiago')->format('d-m-Y_H-i') . '.docx';
            $outPath  = storage_path('app/viaticos/'.$filename);

            $tp->saveAs($outPath);
            @unlink($tplPath); // limpia temporal normalizado

            $viatico->update(['docx_path' => 'viaticos/'.$filename]);

            // Descarga (conservando copia en storage)
            return response()->download($outPath, $filename)->deleteFileAfterSend(false);

        } catch (\Throwable $e) {
            Log::error('Viatico store error', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if (config('app.debug')) {
                return response()->json([
                    'error' => class_basename($e).': '.$e->getMessage(),
                    'at'    => $e->getFile().':'.$e->getLine(),
                ], 500);
            }

            abort(500, 'Error interno al generar el DOCX.');
        }
    }

    /* ======================= Helpers privados ======================= */

    private function vehiculos(): array
    {
        $v = [];
        for ($i=4; $i<=99; $i++) $v[] = 'M'.str_pad((string)$i, 2, '0', STR_PAD_LEFT);
        return $v;
    }

    private function nuevoFolio(): string
    {
        $year  = now('America/Santiago')->year;
        $count = Viatico::whereYear('created_at', $year)->count() + 1;
        return sprintf('VIA-%d-%04d', $year, $count);
    }

    private function mesAno(string|\DateTimeInterface $fecha): string
    {
        $dt = is_string($fecha) ? Carbon::parse($fecha, 'America/Santiago') : Carbon::instance($fecha);
        $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        return $meses[$dt->month-1] . ' de ' . $dt->year;
    }

    private function rutFormateado(string $rut): string
    {
        $rut = strtoupper(preg_replace('/[^0-9K]/','', $rut));
        if (!preg_match('/^(\d+)([K0-9])$/', $rut, $m)) return $rut;
        return number_format((int)$m[1], 0, '', '.') . '-' . $m[2];
    }

    /** Normaliza {{campo}} -> ${campo} en document.xml / headers / footers */
    private function normalizeDocxPlaceholders(string $inputDocxPath): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'tpl_') . '.docx';
        if (!@copy($inputDocxPath, $tmp)) {
            throw new \RuntimeException('No se pudo copiar la plantilla DOCX a tmp.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($tmp) !== true) {
            throw new \RuntimeException('No se pudo abrir DOCX para normalizar (ZipArchive no disponible o archivo corrupto).');
        }

        $targets = ['word/document.xml'];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#^word/(header|footer)\d+\.xml$#', $name)) {
                $targets[] = $name;
            }
        }

        foreach ($targets as $entry) {
            $xml = $zip->getFromName($entry);
            if ($xml === false) continue;
            $zip->addFromString($entry, str_replace(['{{','}}'], ['${','}'], $xml));
        }

        $zip->close();
        return $tmp;
    }
}
