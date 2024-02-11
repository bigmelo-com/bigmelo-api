<?php

namespace App\Listeners\User;

use App\Events\User\UserValidated;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\Plan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CreateLeadFromNewUser
{
    /**
     * Handle the event.
     */
    public function handle(UserValidated $event): void
    {
        $user = $event->user_validated;

        try {
            $lead = Lead::where('user_id', $user->id)->first();

            if (!$lead) {
                $lead = Lead::create([
                    'user_id' => $user->id,
                    'first_name' => $user->name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'country_code' => $user->country_code,
                    'phone_number' => $user->phone_number,
                    'full_phone_number' => $user->full_phone_number,
                ]);
            }

            $organization = Organization::where('name', 'Bigmelo')->first();
            $project = $organization->projects->first();
            $plan = Plan::where('project_id', $project->id)->first();
            
            $lead->projects()->attach($project);
            $lead->remaining_messages = $plan ? $plan->message_limit : $project->message_limit;
            $lead->plan_id = $plan ? $plan->id : null;
            $lead->save();

            Log::info(
                "Listener: CreateLeadFromNewUser, " .
                "organization_id: " . $organization->id . ", " .
                "project_id: " . $project->id . ", " .
                "user_id: " . $user->id . ", " .
                "lead_id: " . $lead->id . ", " .
                "plan_id: " . $plan->id
            );

        } catch (\Throwable $e) {
            Log::error(
                'CreateLeadFromNewUser: Internal error, ' .
                'user_id: ' . $user->id . ', ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}
