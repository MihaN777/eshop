<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Support\Exceptions\ProjectException;
use App\Support\Payment\PaymentData;
use App\Support\Payment\PaymentSystem;
use App\Support\ValueObjects\Price;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class PaymentController extends Controller
{
    public function payment(): RedirectResponse
    {
        try {
            $paymentUrl = PaymentSystem::create(new PaymentData(
                id: '###_ID_###',
                description: 'description',
                returnUrl: route('payment.callback'),
                amount: new Price(1000),
                meta: collect(['product' => 'alias'])
            ))->url();
        } catch (Throwable $e) {
            throw new ProjectException('Ошибка оплаты', $e->getMessage());
        }

        return redirect($paymentUrl);
    }

    public function callback(Request $request): JsonResponse
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
