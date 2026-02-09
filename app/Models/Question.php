<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'category_id',
        'age_group_id',
        'content',
        'type',
        'options',
        'correct_answer',
        'lesson_reference',
        'difficulty',
        'created_by',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'options' => 'array',
        'approved_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function ageGroup(): BelongsTo
    {
        return $this->belongsTo(AgeGroup::class);
    }

    public function questionSetItems(): HasMany
    {
        return $this->hasMany(QuestionSetItem::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
