<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicantRequest; // <-- Importar la clase
use App\Http\Requests\UpdateApplicantRequest; // <-- Importar la clase
use App\Models\Applicant;
use App\Models\Institution;
use App\Models\Status;

use Illuminate\Http\RedirectResponse;

use Illuminate\Support\Facades\DB; // <-- ¡¡AÑADE ESTA LÍNEA!!
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response; // <-- Importar la clase Response

class ApplicantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // ApplicantController.php -> método index()
    public function index(): Response
    {
        // REESCRITURA COMPLETA USANDO EL QUERY BUILDER
        // Esto es como escribir SQL directamente. Es imposible que falle si los IDs existen.
        $applicants = Applicant::with(['institution', 'status'])->paginate(10);

        return Inertia::render('Applicants/Index', [
            'applicants' => $applicants,
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // Pasamos las listas de instituciones y estados para los menús <select>
        // del formulario de React.
        return Inertia::render('Applicants/Create', [
            'institutions' => Institution::orderBy('name')->get(['id', 'name']),
            'statuses' => Status::all(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreApplicantRequest $request): RedirectResponse
    {
        Applicant::create($request->validated());

        // Cambiamos 'success' por 'message' para que coincida con el frontend
        return Redirect::route('applicants.index')->with('message', 'Postulante creado con éxito.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Applicant $applicant): Response
    {
        return Inertia::render('Applicants/Edit', [
            'applicant' => $applicant, // Pasamos el postulante a editar
            'institutions' => Institution::orderBy('name')->get(['id', 'name']),
            'statuses' => Status::all(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateApplicantRequest $request, Applicant $applicant): RedirectResponse
    {
        // El Form Request se encarga de la validación.
        $applicant->update($request->validated());

        return Redirect::route('applicants.index')->with('message', 'Postulante actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
