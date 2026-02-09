<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contestant extends Model
{
    protected $fillable = [
        'user_id',
        'age_group_id',
        'category_id',
        'competition_id',
        'difficulty_level',
        'current_xp',
        'stage_reached',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ageGroup(): BelongsTo
    {
        return $this->belongsTo(AgeGroup::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ContestantResponse::class);
    }

    public function performanceAnalytics(): HasMany
    {
        return $this->hasMany(PerformanceAnalytics::class);
    }
}
