<?php

namespace App\Listeners;

use App\Events\PublishRabbitMQEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Services\RabbitMQService;

class SendRabbitMQMessageListener implements ShouldQueue
{
    public $rabbitmqService;

    /**
     * Create the event listener.
     */
    public function __construct(RabbitMQService $rabbitmqService)
    {
        $this->rabbitmqService = $rabbitmqService;
    }

    /**
     * Handle the event.
     */
    public function handle(PublishRabbitMQEvent $event): void
    {
        $this->rabbitmqService->publish($event->exchange, $event->routingKey, $event->data);
    }
}
