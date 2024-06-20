<?php

namespace App\Listeners\User;

use App\Events\User\LeadStored;
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
            $organization = Organization::where('name', 'Bigmelo')->first();
            $project = $organization->projects->first();
            $lead = Lead::whereHas('projects', function ($q) use($project) {
                    $q->where('project_id', $project->id);
                })->where('full_phone_number', $user->full_phone_number)->orWhere('user_id', $user->id)->first();

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
                $lead->projects()->attach($project);
                $lead->remaining_messages = $project->message_limit;
                $lead->save();

            } elseif (!$lead->user_id) {
                $lead->user_id = $user->id;
                $lead->first_name = $user->name;
                $lead->last_name = $user->last_name;
                $lead->email = $user->email;
                $lead->remaining_messages = $project->message_limit;
                $lead->save();
            }
            

            event(new LeadStored($lead));

            Log::info(
                "Listener: CreateLeadFromNewUser, " .
                "organization_id: " . $organization->id . ", " .
                "project_id: " . $project->id . ", " .
                "user_id: " . $user->id . ", " .
                "lead_id: " . $lead->id . ", "
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