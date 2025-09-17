<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Provider extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Un proveedor puede PERTENECER A MUCHAS encuestas
    public function surveys(): BelongsToMany
    {
        return $this->belongsToMany(Survey::class, 'provider_survey');
    }
}
