<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],

            // El folio debe ser único en la tabla 'applicants' para el año especificado.
            // Esto permite que el mismo folio exista para un año diferente.
            'folio' => ['required', 'integer', Rule::unique('applicants')->where('year', $this->year)],

            'year' => ['required', 'integer', 'date_format:Y'],
            'score' => ['nullable', 'numeric', 'between:1.0,7.0'], // Asumiendo escala de notas chilena

            // El institution_id debe existir en la tabla 'institutions'.
            'institution_id' => ['required', 'integer', 'exists:institutions,id'],

            // El status_id debe existir en la tabla 'statuses'.
            'status_id' => ['required', 'integer', 'exists:statuses,id'],
        ];
    }
}
