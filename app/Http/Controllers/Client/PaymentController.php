<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Support\Payment\PaymentSystem;
use Illuminate\Http\JsonResponse;
use Throwable;

class PaymentController extends Controller
{
    public function __invoke(string $provider): JsonResponse
    {
        try {
            PaymentSystem::setProviderByName($provider);

            $response = PaymentSystem::validate()->response();
        } catch (Throwable $e) {
            return response()->json(
                data: ['error' => $e->getMessage()],
                status: 500
            );
        }

        return $response;
    }
}
