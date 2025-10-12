<?php

namespace App\Support\Flash;

class FlashMessage
{
    protected string $message;
    protected string $class;
    protected string $style;

    public function __construct(string $message, string $class, string $style)
    {
        $this->message = $message;
        $this->class = $class;
        $this->style = $style;
    }

    public function message(): string {
        return $this->message;
    }

    public function class(): string {
        return $this->class;
    }

    public function style(): string {
        return $this->style;
    }
}
