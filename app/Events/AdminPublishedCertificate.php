<?php

namespace App\Events;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminPublishedCertificate
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $certificate;
    public $certifiable;
    /**
     * Create a new event instance.
     */
    public function __construct(
        User $user,
        Certificate $certificate,
        $certifiable
    ) {
        $this->user = $user;
        $this->certificate = $certificate;
        $this->certifiable = $certifiable;
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
