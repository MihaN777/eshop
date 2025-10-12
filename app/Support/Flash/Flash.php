<?php

namespace App\Support\Flash;

use Illuminate\Contracts\Session\Session;

class Flash
{
    public const MESSAGE_KEY = 'ctm_flash_message';
    public const MESSAGE_CLASS_KEY = 'ctm_flash_class';
    public const MESSAGE_STILE_KEY = 'ctm_flash_stile';

    protected Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function get(): ?FlashMessage
    {
        $message = $this->session->get(self::MESSAGE_KEY);

        if (!$message) return null;

        return new FlashMessage(
            $message,
            $this->session->get(self::MESSAGE_CLASS_KEY, ''),
            $this->session->get(self::MESSAGE_STILE_KEY, '')
        );
    }

    public function info(string $message): void
    {
        $this->set($message, 'info');
    }

    public function alert(string $message): void
    {
        $this->set($message, 'alert');
    }

    public function set(string $message, string $flashType): void
    {
        $this->session->flash(self::MESSAGE_KEY, $message);
        $this->session->flash(self::MESSAGE_CLASS_KEY, config("flash.{$flashType}.class", 'undefined_class'));
        $this->session->flash(self::MESSAGE_STILE_KEY, config("flash.{$flashType}.stile", 'undefined_stile'));
    }
}
