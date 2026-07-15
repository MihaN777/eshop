<?php

namespace App\Listeners;

use App\Events\SessionRegenerated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SessionRegeneratedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SessionRegenerated $event): void
    {
        cart()->handleSessionRegenerated($event->oldId, $event->newId);
    }
}
