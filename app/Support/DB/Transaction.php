<?php

namespace App\Support\DB;

use Closure;
use Illuminate\Support\Facades\DB;
use Throwable;

class Transaction
{
    public static function run(
        Closure $callback,
        Closure $finished = null,
        Closure $onError = null,
    ): mixed
    {
        try {
            DB::beginTransaction();

            $result = $callback();

            if (!is_null($finished)) $finished($result);

            DB::commit();

            return $result;
        } catch (Throwable $e) {
            DB::rollBack();

            if (!is_null($onError)) $onError($e);

            throw $e;
        }
    }
}
