<?php

namespace App\Observers;

use App\Jobs\ProductJsonPropertiesJob;
use App\Models\Product;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ProductObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->SetJsonProperties($product);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $this->SetJsonProperties($product);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }

    public function SetJsonProperties(Product $product): void
    {
        ProductJsonPropertiesJob::dispatch($product)
            ->delay(now()->addSeconds(10)); // Отложенка для сидов
    }
}
