<?php

namespace App\Support\Traits;

trait Makeable
{
    public static function make(mixed ...$arguments): self
    {
        return new self(...$arguments);
    }
}
