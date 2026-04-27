<?php

namespace App\Observers;

use App\Models\Order;
use App\Support\Orders\OrderTabCountsCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class OrderObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Order $order): void
    {
        OrderTabCountsCache::refresh();
    }

    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        OrderTabCountsCache::refresh();
    }

    public function deleted(Order $order): void
    {
        OrderTabCountsCache::refresh();
    }

    public function restored(Order $order): void
    {
        OrderTabCountsCache::refresh();
    }

    public function forceDeleted(Order $order): void
    {
        OrderTabCountsCache::refresh();
    }
}
