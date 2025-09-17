<?php

// app/Models/Applicant.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Applicant extends Model
{
    use HasFactory;

    // Proteger contra asignación masiva. ¡Siempre es una buena práctica!
    protected $fillable = [
        'folio',
        'full_name',
        'score',
        'year',
        'institution_id',
        'status_id',
    ];

    // Ayuda a que Eloquent devuelva los datos en el formato correcto siempre
    protected function casts(): array
    {
        return [
            'score' => 'decimal:3',
            'year' => 'integer',
            'folio' => 'integer',
            // --- ¡AÑADE ESTAS LÍNEAS! ---
            // Le decimos a Eloquent: "Trata estos campos SIEMPRE como números enteros".
            'institution_id' => 'integer',
            'status_id' => 'integer',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }
}
