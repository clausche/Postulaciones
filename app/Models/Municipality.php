<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipality extends Model
{
    use HasFactory;
    protected $guarded = []; // Permite la creación masiva de datos

    // Un municipio puede TENER MUCHAS encuestas (una por año)
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }
}
