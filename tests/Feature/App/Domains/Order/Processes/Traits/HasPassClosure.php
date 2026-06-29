<?php

namespace Tests\Feature\App\Domains\Order\Processes\Traits;

use App\Models\Order;
use Closure;

trait HasPassClosure
{
    /**
     * Заглушку для Pipeline.
     * Возвращает замыкание следующего шага Pipeline.
     */
    private function pass(): Closure
    {
        return fn(Order $order) => $order;
    }
}
