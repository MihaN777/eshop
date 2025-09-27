<?php

namespace App\Support\Flash;

class FlashMessage
{
    protected string $message;
    protected string $class;

    public function __construct(string $message, string $class)
    {
        $this->message = $message;
        $this->class = $class;
    }

    public function message(): string {
        return $this->message;
    }

    public function class(): string {
        return $this->class;
    }
}
