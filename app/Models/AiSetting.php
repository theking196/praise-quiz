<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $fillable = [
        'mix_new_percentage',
        'mix_missed_percentage',
        'mix_old_percentage',
        'max_difficulty_by_age_group',
    ];

    protected $casts = [
        'max_difficulty_by_age_group' => 'array',
    ];

    public $timestamps = false;
}
