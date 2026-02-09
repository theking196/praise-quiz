<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Grouped question set for drills or competition sessions.
 */
class QuestionSet extends Model
{
    protected $fillable = [
        'competition_id',
        'category_id',
        'age_group_id',
        'name',
        'session_type',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(QuestionSetItem::class);
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function ageGroup(): BelongsTo
    {
        return $this->belongsTo(AgeGroup::class);
    }
}
