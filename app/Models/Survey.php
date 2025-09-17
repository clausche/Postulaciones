<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Survey extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Una encuesta PERTENECE A un único municipio
    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    // Una encuesta puede tener MUCHOS proveedores
    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(Provider::class, 'provider_survey');
    }

    // Una encuesta puede tener MUCHAS plataformas
    public function platforms(): BelongsToMany
    {
        return $this->belongsToMany(Platform::class, 'platform_survey');
    }
}
