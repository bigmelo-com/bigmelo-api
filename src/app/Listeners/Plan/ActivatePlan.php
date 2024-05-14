<?php

namespace App\Listeners\Plan;

use App\Events\Message\BigmeloMessageStored;
use Illuminate\Queue\InteractsWithQueue;
use App\Repositories\MessageRepository;
use Illuminate\Support\Facades\Log;
use App\Events\Plan\PlanActivated;
use Illuminate\Support\Facades\DB;
use App\Models\Project;
use App\Models\Lead;
use App\Models\Plan;
use Carbon\Carbon;

class ActivatePlan
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PlanActivated $event): void
    {
        $transaction = $event->transaction;
        $project = Project::find(1);
        $message_repository = new MessageRepository();
        
        try {
            $plan = Plan::find($transaction->plan_id);
            $lead = Lead::find($transaction->lead_id);
            $lead->plan_id = $plan->id;
            $lead->remaining_messages = $plan->message_limit;
            $lead->plan_start_at = Carbon::now();
            $lead->save();
            
            $transaction->status = 'completed';
            $transaction->save();
            
            $twilio_plan_purchased_template = "Hola {$lead->first_name}! 🌟 ya puedes disfrutar de tu nuevo plan! ¿Sobre qué te gustaría conversar?";
            
            $message = $message_repository->storeMessage(
                lead_id: $lead->id,
                project_id: $project->id,
                content: $twilio_plan_purchased_template,
                source: 'Admin'
            );

            event(new BigmeloMessageStored($message));

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