<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\User;

class GenerateRegistrationPins
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $count;
    
    public $user;

    public $pinType;

    public $election;
    

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($pinType, $count, User $user, $election)
    {
        $this->count = $count;
        $this->user = $user;
        $this->pinType = $pinType;
        $this->election = $election;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
