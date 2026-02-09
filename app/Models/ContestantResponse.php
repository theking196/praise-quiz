<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContestantResponse extends Model
{
    protected $fillable = [
        'contestant_id',
        'question_id',
        'response',
        'is_correct',
        'time_taken',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public $timestamps = false;

    public function contestant(): BelongsTo
    {
        return $this->belongsTo(Contestant::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
