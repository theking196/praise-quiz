<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aggregated analytics snapshots for a contestant.
 */
class PerformanceAnalytics extends Model
{
    protected $fillable = [
        'contestant_id',
        'total_score',
        'average_time',
        'weak_topics',
        'learning_patterns',
        'badges_earned',
        'stage_reached',
    ];

    protected $casts = [
        'weak_topics' => 'array',
        'learning_patterns' => 'array',
        'badges_earned' => 'array',
    ];

    public $timestamps = false;

    public function contestant(): BelongsTo
    {
        return $this->belongsTo(Contestant::class);
    }
}
