<?php

namespace App\Listeners\Plan;

use App\Events\Plan\PlanActivated;
use App\Models\Lead;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivatePlan
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PlanActivated $event): void
    {
        $transaction = $event->transaction;
        try {
            $lead = Lead::find($transaction->lead_id);
            $plan = Plan::find($transaction->plan_id);
            $lead->plan_id = $plan->id;
            $lead->remaining_messages = $plan->message_limit;
            $lead->plan_start_at = Carbon::now();
            $lead->save();

            DB::table('lead_plan_logs')->insert([
                'lead_id'           => $lead->id,
                'plan'              => $plan->id,
                'transaction_id'    => $transaction->id
            ]);

            Log::info(
                "Listener: Activate Plan, " .
                "transaction_id: " . $transaction->id 
            );
            
           return;

        } catch (\Throwable $e) {
            Log::error(
                'PlanActivated: Internal error, ' .
                "transaction_id: " . $transaction->id .
                'error: ' . $e->getMessage()
            );
        }
    }
}