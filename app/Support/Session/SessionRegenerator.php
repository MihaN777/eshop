<?php

namespace App\Support\Session;

use App\Events\SessionRegenerated;
use Closure;

class SessionRegenerator
{
    public static function run(Closure $callback = null): void
    {
        $oldId = request()->session()->getId();

        // request()->session()->regenerate();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        if (!is_null($callback)) $callback();

        $newId = request()->session()->getId();

        event(new SessionRegenerated($oldId, $newId));
    }
}