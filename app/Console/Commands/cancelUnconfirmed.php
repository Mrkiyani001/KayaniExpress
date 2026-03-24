<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class cancelUnconfirmed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:cancel-unconfirmed';
   
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel orders that are still processing after timeout';

    /**
     * Execute the console command.
     */
    public function __construct(private OrderService $orderService){
        parent::__construct();

    }
    public function handle()
    {
        $cancelled = $this->orderService->cancelstuckorder();
        $this->info("{$cancelled['count']} orders cancelled successfully.");
    }
}
