<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DispatchNotification implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $title;
    public $message;
    public $imageUrl;
    public $id;

    public function __construct($title, $message, $imageUrl, $id)
    {
        $this->title = $title;
        $this->message = $message;
        $this->imageUrl = $imageUrl;
        $this->id = $id;
    }

    public function broadcastOn()
    {
        return ['blaack-forest'];
    }

    public function broadcastAs()
    {
        return 'dispatch-'.$this->id;
    }

}
