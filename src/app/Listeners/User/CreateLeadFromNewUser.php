<?php

namespace App\Listeners\User;

use App\Events\User\UserStored;
use App\Models\Lead;
use App\Models\Organization;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CreateLeadFromNewUser
{
    /**
     * Handle the event.
     */
    public function handle(UserStored $event): void
    {
        $user = $event->new_user;

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

            $lead->projects()->attach($project);

            Log::info(
                "Listener: CreateLeadFromNewUser, " .
                "organization_id: " . $organization->id . ", " .
                "project_id: " . $project->id . ", " .
                "user_id: " . $user->id . ", " .
                "lead_id: " . $lead->id
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
