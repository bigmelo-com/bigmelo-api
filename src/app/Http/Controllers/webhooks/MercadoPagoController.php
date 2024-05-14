<?php

namespace App\Http\Controllers\webhooks;

use App\Events\Plan\PlanActivated;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{
    /**
     * Get and stored payment from Mercado Pago
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function paymentWebhook(Request $request): JsonResponse
    {
        try {
            $payment = Payment::create([
                'payment_id' => $request->data["id"]
            ]);
            $transaction = Transaction::where('payment_id', $payment->payment_id)->first();
            
            if($transaction){
                event(new PlanActivated($transaction));
            };

            return response()->json([
                'message' => 'Request recived successfully'
            ], 200);

        } catch (\Throwable $e) {
            Log::error(
                'MercadoPagoController - paymentWebhook, ' .
                'Error: ' . $e->getMessage() . ',' .
                'Payment_id: ' . $request->data["id"]
            );
            
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
