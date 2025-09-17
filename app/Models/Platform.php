<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Platform extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Una plataforma puede PERTENECER A MUCHAS encuestas
    public function surveys(): BelongsToMany
    {
        return $this->belongsToMany(Survey::class, 'platform_survey');
    }
}
