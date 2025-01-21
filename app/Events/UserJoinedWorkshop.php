<?php

namespace App\Events;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserJoinedWorkshop
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    public $workshop;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Workshop $workshop)
    {
        $this->user = $user;
        $this->workshop = $workshop;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('channel-name')];
    }
}
