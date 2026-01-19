<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HojaPresenceUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public string $userName,
        public ?string $userAvatar,
        public int $hojaId,
        public string $action // 'joined' or 'left'
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('hojas.global');
    }
}
