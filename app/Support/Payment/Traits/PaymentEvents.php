<?php

namespace App\Support\Payment\Traits;

use Closure;

trait PaymentEvents
{
    protected static Closure $onCreating;
    protected static Closure $onCreated;
    protected static Closure $onSuccess;
    protected static Closure $onValidatingFailed;
    protected static Closure $onError;

    public static function onCreating(Closure $event): void
    {
        self::$onCreating = $event;
    }

    public static function onCreated(Closure $event): void
    {
        self::$onCreated = $event;
    }

    public static function onSuccess(Closure $event): void
    {
        self::$onSuccess = $event;
    }

    public static function onValidatingFailed(Closure $event): void
    {
        self::$onValidatingFailed = $event;
    }

    public static function onError(Closure $event): void
    {
        self::$onError = $event;
    }
}
