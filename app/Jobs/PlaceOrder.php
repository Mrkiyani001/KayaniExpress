<?php

namespace App\Jobs;

use App\Services\OrderService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PlaceOrder implements ShouldQueue
{
    use Dispatchable,InteractsWithQueue,Queueable,SerializesModels;

    public int $tries = 3;   // retry count
    public int $backoff = 5; // wait time in seconds
    protected $user_id;
    protected $address_id;
    protected $payment_method;
    protected $shipping_cost;
    /**
     * Create a new job instance.
     */
    public function __construct($user_id, $address_id, $payment_method, $shipping_cost)
    {
        $this->user_id = $user_id;
        $this->address_id = $address_id;
        $this->payment_method = $payment_method;
        $this->shipping_cost = $shipping_cost;
    }

    /**
     * Execute the job.
     */
    public function handle(OrderService $orderService): void
    {
        $orderService->processingorder(
            $this->user_id,
            $this->address_id,
            $this->payment_method,
            $this->shipping_cost
        );
    }
    public function failed(Exception $exception): void
    {
        Log::error('Order placed failed', [
            'error' => $exception->getMessage(),
        ]);
    throw $exception;
}
}