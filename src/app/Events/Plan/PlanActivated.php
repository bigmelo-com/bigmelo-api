<?php

namespace App\Events\Plan;

use App\Models\Lead;
use App\Models\Transaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PlanActivated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Transaction made.
     *
     * @var Transaction
     */
    public Transaction $transaction;
    
    /**
     * User activate their plan.
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;

        Log::info(
            "Event: User activate plan, " .
            "transaction_id: " . $transaction->id 
        );
    }
}
