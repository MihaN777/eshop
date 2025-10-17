<?php

namespace App\Support\Traits\DTOs;

trait Makeable
{
    public static function make(mixed ...$arguments): self
    {
        return new self(...$arguments);
    }
}
