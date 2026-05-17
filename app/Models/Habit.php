<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Habit extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id',
        'title',
        'is_completed',
        'updated_by',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(HabitBoard::class, 'board_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
