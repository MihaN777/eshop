<?php

namespace App\Support\Cart\Contracts;

interface CartIdentityStorageContract
{
    public function get(): string;
}