<?php

namespace App\Listeners;

use App\Services\Contracts\Client\CartServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MergeGuestCartListener
{
    /**
     * Create the event listener.
     */
    public function __construct(private CartServiceInterface $cartService){}

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        
        $guestSessionId = session()->getId();
        $this->cartService->mergeGuestCart($guestSessionId);
    }
}
