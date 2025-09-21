<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Viatico extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre','rut','escalafon','grado','funcion',
        'lugar','motivo','dia_salida','hora_salida',
        'dia_regreso','hora_regreso','vehiculo','patente',
        'resolucion','mes_ano','docx_path'
    ];

    protected $casts = [
        'dia_salida'  => 'date',
        'dia_regreso' => 'date',
    ];
}
