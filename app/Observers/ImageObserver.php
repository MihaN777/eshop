<?php

namespace App\Observers;

use App\Models\Image;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Storage;

class ImageObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Image "created" event.
     */
    public function created(Image $image): void
    {
        //
    }

    /**
     * Handle the Image "updated" event.
     */
    public function updated(Image $image): void
    {
        //
    }

    /**
     * Handle the Image "deleted" event.
     * Событие не работает при каскадном удалении!
     */
    public function deleted(Image $image): void
    {
        $msgExists = "Не существует файл для удаления: {$image->path}";
        $msgDelete = "Не удалось удалить файл: {$image->path}";

        if (!Storage::exists($image->path))
        {
            logger()->info($msgExists);
            logger()->channel('telegram')->info($msgExists);
            return;
        }

        if (!Storage::delete($image->path)) {
            logger()->info($msgDelete);
            logger()->channel('telegram')->info($msgDelete);
        }
    }

    /**
     * Handle the Image "restored" event.
     */
    public function restored(Image $image): void
    {
        //
    }

    /**
     * Handle the Image "force deleted" event.
     */
    public function forceDeleted(Image $image): void
    {
        //
    }
}
