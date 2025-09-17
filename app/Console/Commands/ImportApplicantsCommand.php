<?php

namespace App\Console\Commands;

use App\Models\Applicant;
use App\Models\Institution;
use App\Models\Status;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportApplicantsCommand extends Command
{
    protected $signature = 'app:import-applicants {file : La ruta al archivo CSV} {--year= : El año del proceso de postulación}';
    protected $description = 'Importa los postulantes desde el archivo CSV de resultados';

    public function handle(): int
    {
        $filePath = $this->argument('file');
        $year = $this->option('year') ?: date('Y');

        if (!file_exists($filePath)) {
            $this->error("El archivo no existe en la ruta: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Iniciando la importación para el año {$year} desde {$filePath}...");

        try {
            // Usamos una transacción para asegurar la integridad de los datos
            DB::transaction(function () use ($filePath, $year) {

                // 1. Pre-cargar los estados que usaremos
                $statuses = [
                    'selected'     => Status::firstOrCreate(['slug' => 'selected'], ['name' => 'Seleccionado']),
                    'waitlisted'   => Status::firstOrCreate(['slug' => 'waitlisted'], ['name' => 'En Lista de Espera']),
                    'not-selected' => Status::firstOrCreate(['slug' => 'not-selected'], ['name' => 'No Seleccionado']),
                    'disqualified' => Status::firstOrCreate(['slug' => 'disqualified'], ['name' => 'Fuera de Bases']),
                ];

                // 2. PRE-ANÁLISIS: Obtener los folios de la lista de espera (Anexo 5)
                $waitlistFolios = $this->getWaitlistFolios($filePath);
                $this->info(count($waitlistFolios) . ' postulantes encontrados en la lista de espera.');

                // 3. ANÁLISIS PRINCIPAL: Procesar el listado completo (Anexo 1)
                $records = $this->getRecordsFromAnexo($filePath, 'ANEXO 1: POSTULACIONES RECIBIDAS');

                $this->info(count($records) . ' postulantes encontrados en el listado general.');
                $bar = $this->output->createProgressBar(count($records));
                $bar->start();

                foreach ($records as $record) {
                    $folio = (int)$record['Folio'];
                    $estadoOriginal = trim($record['Estado Postulación']);

                    // Lógica para asignar el estado correcto
                    $statusId = match ($estadoOriginal) {
                        'Seleccionada' => $statuses['selected']->id,
                        'Fuera de bases' => $statuses['disqualified']->id,
                        'No seleccionada' => in_array($folio, $waitlistFolios)
                            ? $statuses['waitlisted']->id
                            : $statuses['not-selected']->id,
                        default => null,
                    };

                    if (is_null($statusId)) continue; // Omitir si el estado no es reconocido

                    // Limpieza de datos
                    $institutionName = str_replace(';', '', $record['Institución']);
                    $score = $record['Puntaje'] === '-' ? null : (float)str_replace(',', '.', trim($record['Puntaje']));

                    $institution = Institution::firstOrCreate(['name' => $institutionName]);

                    // Usamos updateOrCreate para evitar duplicados si se corre el comando de nuevo
                    Applicant::updateOrCreate(
                        ['folio' => $folio],
                        [
                            'full_name'      => trim($record['Nombre']),
                            'score'          => $score,
                            'year'           => $year,
                            'institution_id' => $institution->id,
                            'status_id'      => $statusId,
                        ]
                    );
                    $bar->advance();
                }
                $bar->finish();
                $this->newLine(2);
            });

        } catch (\Exception $e) {
            $this->error("Ocurrió un error durante la importación: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info('¡Importación completada con éxito!');
        return Command::SUCCESS;
    }

    /**
     * Extrae los registros de un anexo específico del archivo CSV.
     */
    private function getRecordsFromAnexo(string $filePath, string $anexoTitle): array
    {
        $csvContent = file($filePath);
        $records = [];
        $isAnexoActive = false;

        // La cabecera se espera justo después del título del anexo
        $headerLine = null;

        foreach ($csvContent as $lineNumber => $lineContent) {
            $lineContent = trim($lineContent);

            // Si encontramos un título de anexo
            if (str_contains($lineContent, 'ANEXO ')) {
                // Si ya estábamos en un anexo activo, lo desactivamos
                if ($isAnexoActive) {
                    $isAnexoActive = false;
                    $headerLine = null;
                }
                // Si este es el anexo que buscamos, lo activamos
                if (str_contains($lineContent, $anexoTitle)) {
                    $isAnexoActive = true;
                    // La siguiente línea debería ser la cabecera
                    $headerLine = $lineNumber + 1;
                }
                continue; // Saltamos la línea de título
            }

            // Si no estamos en el anexo correcto, ignoramos la línea
            if (!$isAnexoActive) {
                continue;
            }

            // Si esta es la línea de cabecera, la ignoramos
            if ($lineNumber === $headerLine) {
                continue;
            }

            // Si es una línea vacía, la ignoramos
            if (empty($lineContent)) {
                continue;
            }

            // Procesamos la línea como un registro de datos
            // Usamos str_getcsv para procesar una sola línea con el delimitador correcto
            $data = str_getcsv($lineContent, ';');

            // Asumimos un orden de columnas fijo para robustez
            $records[] = [
                '#' => $data[0] ?? null,
                'Folio' => $data[1] ?? null,
                'Nombre' => $data[2] ?? null,
                'Institución' => $data[3] ?? null,
                'Puntaje' => $data[4] ?? null,
                'Estado Postulación' => $data[5] ?? null,
            ];
        }

        return $records;
    }

    /**
     * Extrae solo los folios del Anexo 5 (Lista de Espera).
     */
    private function getWaitlistFolios(string $filePath): array
    {
        $records = $this->getRecordsFromAnexo($filePath, 'ANEXO 5: SELECCIONADAS EN LISTA DE ESPERA');
        return array_column($records, 'Folio');
    }
}
