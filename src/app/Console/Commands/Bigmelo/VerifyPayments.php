<?php

namespace App\Console\Commands\Bigmelo;

use App\Classes\MercadoPago\MercadoPagoClient;
use App\Events\Plan\PlanActivated;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class VerifyPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bigmelo:verify-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify plan payments to activate users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Payment::all()->each(function (Payment $payment) {
                $client = new MercadoPagoClient();
                $mp_payment = $client->getPaymentFromMercadoPago($payment->payment_id);
                $transaction = Transaction::find($mp_payment->external_reference);
    
                if (!$transaction->payment_id && $mp_payment->status === 'approved') {
                    $transaction->payment_id = $payment->payment_id;
                    $transaction->save();
    
                    event(new PlanActivated($transaction));
                }
            });
    
            echo "Payments verified \n";

            Log::info(
                'VerifyPayments: Payments verified successfully'
            );

        } catch (\Throwable $e) {
            Log::error(
                'VerifyPayments: Internal error, ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}
