<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HabitBoard extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'owner_id',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'board_collaborators', 'board_id', 'user_id')
                    ->withTimestamps();
    }

    public function habits(): HasMany
    {
        return $this->hasMany(Habit::class, 'board_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(BoardVersion::class, 'board_id');
    }
}
