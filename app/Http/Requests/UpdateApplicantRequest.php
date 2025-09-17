<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // <-- ¡Importante!

class UpdateApplicantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Permitir a cualquier usuario autenticado
    }

    public function rules(): array
    {
        return [
            'folio' => [
                'required',
                'integer',
                // La regla unique debe ignorar el folio del postulante actual
                Rule::unique('applicants')->ignore($this->applicant->id),
            ],
            'full_name' => 'required|string|max:255',
            'score' => 'nullable|numeric|min:0',
            'year' => 'required|integer|min:1900|max:2100',
            'institution_id' => 'required|exists:institutions,id',
            'status_id' => 'required|exists:statuses,id',
        ];
    }
}
