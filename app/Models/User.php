<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * User entity for authentication and role-based access.
 */
class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    public function contestants(): HasMany
    {
        return $this->hasMany(Contestant::class);
    }
}
