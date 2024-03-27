<?php

namespace App\Listeners\User;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\Message\BigmeloMessageStored;
use Illuminate\Support\Facades\Log;
use App\Events\User\UserStored;
use App\Models\Lead;
use App\Models\Project;
use App\Repositories\MessageRepository;
use Carbon\Carbon;

class SendValidationCode implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public int $tries = 5;

    /**
     * The number of seconds before the job should be retried.
     *
     * @var int
     */
    public int $retryAfter = 10;

    /**
     * Handle the event.
     */
    public function handle(UserStored $event): void
    {
        $user = $event->new_user;
        $project = Project::find(1);
        $message_repository = new MessageRepository();
        try{
            $lead = $project->leads->where('full_phone_number', $user->full_phone_number)->first();

            if (!$lead) {
                $lead = Lead::create([
                    'country_code'          => $user->country_code,
                    'phone_number'          => $user->phone_number,
                    'full_phone_number'     => $user->full_phone_number,
                ]);

                $lead->projects()->attach($project);
                $lead->remaining_messages = config('not_registered_user_message_limit' ,5);
                $lead->save();
            }

            $message = $message_repository->storeMessage(
                lead_id: $lead->id,
                project_id: $project->id,
                content: config('bigmelo.message.validation_code_message') . "*" . $user->validation_code . "*",
                source: 'Admin'
            );
            $user->validation_code_sent_at = Carbon::now();
            $user->save();

            event(new BigmeloMessageStored($message));

            Log::info(
                'Validation code sent to whatsapp, ' .
                'from: ' . $project->phone_number . ', ' .
                'to: ' . $user->full_phone_number . ', '
            );
        } catch (\Throwable $e) {
            Log::error(
                'SendSmsMessage: Internal error, ' .
                'from: ' . $project->phone_number . ', ' .
                'to: ' . $user->full_phone_number . ', ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}
