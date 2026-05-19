<?php

namespace App\Events;

use App\Models\HabitBoard;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BoardInvitationEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $boardData;
    public $invitedUserId;

    public function __construct(HabitBoard $board, $invitedUserId)
    {
        $this->boardData = [
            'id' => $board->id,
            'title' => $board->title,
            'description' => $board->description ?? 'Tidak ada deskripsi.',
            'owner_name' => $board->owner->name,
            'url' => route('boards.show', $board),
        ];
        $this->invitedUserId = $invitedUserId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->invitedUserId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'board.invited';
    }
}
