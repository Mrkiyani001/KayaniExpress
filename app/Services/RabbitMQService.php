<?php
namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQService{

public function publish($exchange, $routingKey, $data){
    try{
    $connection = new AMQPStreamConnection(
        env('RABBITMQ_HOST'),
        env('RABBITMQ_PORT'),
        env('RABBITMQ_LOGIN'),
        env('RABBITMQ_PASSWORD')
    );
    $channel = $connection->channel();
    $channel->exchange_declare($exchange, 'topic', false, true, false);
    $msg = new AMQPMessage(
        json_encode($data),
        ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
    );
    $channel->basic_publish($msg, $exchange, $routingKey);
    $channel->close();
    $connection->close();
    }catch(Exception  $e){
        Log::error('RabbitMQ Error: ' . $e->getMessage());
    }
}
}