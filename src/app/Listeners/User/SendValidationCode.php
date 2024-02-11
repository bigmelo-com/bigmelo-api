<?php

namespace App\Listeners\User;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Classes\Twilio\TwilioClient;
use Illuminate\Support\Facades\Log;
use App\Events\User\UserStored;
use App\Events\User\UserValidated;
use App\Models\Project;
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
        try{
            $twilio_client = new TwilioClient($project->phone_number);
            $twilio_client->sendSmsMessage($user->full_phone_number, config('bigmelo.message.validation_code_message') . $user->validation_code);
            $user->validation_code_sent_at = Carbon::now();
            $user->save();

            Log::info(
                'Message sent to whatsapp, ' .
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
