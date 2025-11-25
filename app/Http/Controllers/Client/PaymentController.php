<?php

namespace App\Http\Controllers\Client;

use App\Domains\Order\States\Payment\PaidPaymentState;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Support\Payment\PaymentSystem;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PaymentController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $response = PaymentSystem::validate()->response();

            // $paymentId = $request->get("payment_id");
            //
            // $payment = Payment::query()
            //     ->where('payment_id', $paymentId)
            //     ->first();
            //
            // if (!$payment) throw new Exception('Payment not found');
            //
            // $payment->state->transitionTo(PaidPaymentState::class);
        } catch (Throwable $e) {
            return response()->json(
                data: ['error' => $e->getMessage()],
                status: 500
            );
        }

        return $response;
    }
}
