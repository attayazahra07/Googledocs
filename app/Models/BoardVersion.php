<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id',
        'user_id',
        'action',
        'description',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(HabitBoard::class, 'board_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
