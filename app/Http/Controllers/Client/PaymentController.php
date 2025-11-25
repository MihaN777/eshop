<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Support\Payment\PaymentSystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PaymentController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
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
