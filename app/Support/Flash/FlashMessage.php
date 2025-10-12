<?php

namespace App\Support\Flash;

class FlashMessage
{
    protected string $message;
    protected string $class;
    protected string $stile;

    public function __construct(string $message, string $class, string $stile)
    {
        $this->message = $message;
        $this->class = $class;
        $this->stile = $stile;
    }

    public function message(): string {
        return $this->message;
    }

    public function class(): string {
        return $this->class;
    }

    public function stile(): string {
        return $this->stile;
    }
}
