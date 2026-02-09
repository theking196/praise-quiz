<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    protected $fillable = [
        'year',
        'start_date',
        'end_date',
    ];

    public function contestants(): HasMany
    {
        return $this->hasMany(Contestant::class);
    }

    public function questionSets(): HasMany
    {
        return $this->hasMany(QuestionSet::class);
    }
}
