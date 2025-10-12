<?php

namespace App\Support\Flash;

use Illuminate\Contracts\Session\Session;

class Flash
{
    public const MESSAGE_KEY = 'ctm_flash_message';
    public const MESSAGE_CLASS_KEY = 'ctm_flash_class';
    public const MESSAGE_STYLE_KEY = 'ctm_flash_style';

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
            $this->session->get(self::MESSAGE_STYLE_KEY, '')
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
        $this->session->flash(self::MESSAGE_STYLE_KEY, config("flash.{$flashType}.style", 'undefined_style'));
    }
}
