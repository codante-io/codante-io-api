<?php

namespace App\Events;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserCommented
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $comment;
    public $commentable;
    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Comment $comment, $commentable)
    {
        $this->user = $user;
        $this->comment = $comment;
        $this->commentable = $commentable;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel("channel-name")];
    }
}
