<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgeGroup extends Model
{
    protected $fillable = [
        'name',
        'min_age',
        'max_age',
    ];

    public $timestamps = false;

    public function contestants(): HasMany
    {
        return $this->hasMany(Contestant::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function questionSets(): HasMany
    {
        return $this->hasMany(QuestionSet::class);
    }
}
