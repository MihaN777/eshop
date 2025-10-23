<?php

namespace App\Observers;

use App\Models\Brand;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Storage;

class BrandObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Brand "created" event.
     */
    public function created(Brand $brand): void
    {
        //
    }

    /**
     * Handle the Brand "updated" event.
     */
    public function updated(Brand $brand): void
    {
        //
    }

    /**
     * Handle the Brand "deleted" event.
     * Событие не работает при каскадном удалении!
     */
    public function deleted(Brand $brand): void
    {
        $msgExists = "Не существует файл для удаления: {$brand->image}";
        $msgDelete = "Не удалось удалить файл: {$brand->image}";

        if (!Storage::exists($brand->image))
        {
            logger()->info($msgExists);
            logger()->channel('telegram')->info($msgExists);
            return;
        }

        if (!Storage::delete($brand->image)) {
            logger()->info($msgDelete);
            logger()->channel('telegram')->info($msgDelete);
        }
    }

    /**
     * Handle the Brand "restored" event.
     */
    public function restored(Brand $brand): void
    {
        //
    }

    /**
     * Handle the Brand "force deleted" event.
     */
    public function forceDeleted(Brand $brand): void
    {
        //
    }
}
