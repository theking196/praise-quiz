<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionHistory extends Model
{
    protected $fillable = [
        'contestant_id',
        'question_id',
        'asked_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'asked_at' => 'datetime',
    ];

    public function contestant(): BelongsTo
    {
        return $this->belongsTo(Contestant::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
