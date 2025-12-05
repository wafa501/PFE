<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PostThresholdExceeded implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $postId;

    public function __construct($postId)
    {
        $this->postId = $postId;
    }

    public function broadcastOn()
    {
        return new Channel('notifications');
    }

    public function broadcastWith()
    {
        return [
            'postId' => $this->postId,
        ];
    }
}
