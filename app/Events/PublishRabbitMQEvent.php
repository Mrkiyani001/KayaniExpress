<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PublishRabbitMQEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $exchange;
    public $routingKey;
    public $data;

    /**
     * Create a new event instance.
     */
    public function __construct($exchange, $routingKey, $data)
    {
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
        $this->data = $data;
    }
}
