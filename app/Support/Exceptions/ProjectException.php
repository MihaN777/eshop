<?php

namespace App\Support\Exceptions;

use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProjectException extends DomainException
{
    protected string $rawMessage;

    public function __construct(string $flashMessage, string $rawMessage = "")
    {
        $this->rawMessage = $rawMessage;
        parent::__construct($flashMessage);
    }

    public function render(Request $request): Response|RedirectResponse
    {
        flash()->alert($this->getMessage());

        return session()->previousUrl()
            ? back()
            : redirect()->route('home');
    }

    public function report(): void
    {
        if (!empty($this->rawMessage)) {
            $msg = "[LINE {$this->getLine()}] {$this->getFile()} >>> {$this->rawMessage}";

            logger()->error($msg);
            logger()->channel('telegram')->error($msg);
        }
    }
}
