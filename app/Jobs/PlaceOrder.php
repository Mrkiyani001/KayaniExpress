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
    protected $order;
    protected $coupon_id;
    /**
     * Create a new job instance.
     */
    public function __construct($order, $coupon_id = null)
    {
        $this->order = $order;
        $this->coupon_id = $coupon_id;
    }

    /**
     * Execute the job.
     */
    public function handle(OrderService $orderService): void
    {
        $orderService->processOrder($this->order, $this->coupon_id);
    }
    public function failed(Exception $exception): void
    {
        Log::error('Order placed failed', [
            'error' => $exception->getMessage(),
        ]);
    throw $exception;
}
}