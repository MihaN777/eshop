<?php

namespace App\Domains\Cart\StorageIdentities;

use App\Support\Cart\Contracts\CartIdentityStorageContract;

class SessionIdentityStorage implements CartIdentityStorageContract
{
    public function get(): string
    {
        return session()->getId();
    }
}