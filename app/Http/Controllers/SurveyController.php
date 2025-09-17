<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Survey;
use App\Models\Municipality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // En app/Http/Controllers/SurveyController.php

public function index()
{
    // Usamos 'cache' para que estas consultas no se ejecuten cada vez que alguien carga la página.
    // Se guardarán por 60 minutos (3600 segundos).
    $stats = cache()->remember('survey_stats', 3600, function () {
        return [
            'total_municipalities' => Municipality::count(),
            'surveys_completed' => Survey::count(),
            'avg_computers' => round(Survey::avg('total_computadores'), 2),
            'cloud_adoption_percentage' => round((Survey::where('usa_servidores_cloud', true)->count() / Survey::count()) * 100, 2),
            'streaming_adoption_percentage' => round((Survey::where('sesiones_concejo_streaming', true)->count() / Survey::count()) * 100, 2),
            'main_internet_providers' => Survey::select('proveedor_internet_principal', DB::raw('count(*) as total'))
                                                ->whereNotNull('proveedor_internet_principal')
                                                ->groupBy('proveedor_internet_principal')
                                                ->orderBy('total', 'desc')
                                                ->limit(5)
                                                ->get(),
        ];
    });

    return Inertia::render('Surveys/Index', [
        'stats' => $stats,
    ]);
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Survey $survey) // Gracias a Laravel, $survey ya es el registro de la BD
{
    // Cargamos todas las relaciones que queremos mostrar
    $survey->load('municipality', 'providers', 'platforms');

    // Renderizamos el componente 'show.tsx' y le pasamos la encuesta
    return Inertia::render('Surveys/Show', [
        'survey' => $survey
    ]);
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Survey $survey)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Survey $survey)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Survey $survey)
    {
        //
    }
}
