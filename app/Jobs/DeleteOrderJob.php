<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class DeleteOrderJob implements ShouldQueue
{
    use Queueable , InteractsWithQueue , Dispatchable , SerializesModels; 

    public  $order;
    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::find($this->order->id);
        if ($order && $order->status === 'canceled'){
            $order->delete();
        } 
    }
}
