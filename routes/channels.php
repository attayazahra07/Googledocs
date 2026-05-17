<?php

use App\Models\HabitBoard;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('board.{boardId}', function ($user, $boardId) {
    $board = HabitBoard::find($boardId);
    
    // Check if user is owner or collaborator
    if ($board && ($board->owner_id === $user->id || $board->collaborators->contains($user->id))) {
        return [
            'id' => $user->id,
            'name' => $user->name,
        ];
    }
    
    return null;
});
