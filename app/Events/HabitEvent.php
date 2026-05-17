<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HabitEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $habitData;
    public $action; // 'created', 'updated', 'deleted'
    public $boardId;
    public $userId;
    public $logData; // Real-time version history log

    public function __construct($habitData, $action, $boardId, $userId, $logData = null)
    {
        $this->habitData = $habitData;
        $this->action = $action;
        $this->boardId = $boardId;
        $this->userId = $userId;
        $this->logData = $logData;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('board.' . $this->boardId),
        ];
    }
}
