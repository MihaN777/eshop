<?php

namespace App\Support\Exceptions;

use DomainException;
use Illuminate\Http\RedirectResponse;

class ProjectException extends DomainException
{
    protected string $rawMessage;

    public function __construct(string $flashMessage, string $rawMessage = "")
    {
        $this->rawMessage = $rawMessage;
        parent::__construct($flashMessage);
    }

    public function render(): RedirectResponse
    {
        flash()->alert($this->getMessage());
        return back();
    }

    public function report(): void
    {
        if(!empty($this->rawMessage)) {
            logger()->error($this->rawMessage);
            logger()->channel('telegram')->error($this->rawMessage);
        }
    }
}
