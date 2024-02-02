<?php

namespace App\Listeners\User;

use App\Classes\Twilio\TwilioClient;
use App\Events\User\UserStored;
use App\Models\Project;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWelcomeMessageToWhatsapp implements ShouldQueue
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
        $project = Project::find(1);
        $user = $event->new_user;
        $twilio_welcome_template = "Hola {$user->name}! 🌟 Bienvenido a Bigmelo! 🚀 Estamos aquí con el poder de la inteligencia artificial para ayudarte. ¿En qué puedo asistirte hoy? 😊";

        try {
            $twilio_client = new TwilioClient($project->phone_number);
            $twilio_client->sendMessageToWhatsapp($user->full_phone_number, $twilio_welcome_template);

            Log::info(
                'Welcome message sent to whatsapp, ' .
                'from: ' . $project->phone_number . ', ' .
                'to: ' . $user->full_phone_number . ', '
            );

        } catch (\Throwable $e) {
            Log::error(
                'SendWelcomeMessageToWhatsapp: Internal error, ' .
                'from: ' . $project->phone_number . ', ' .
                'to: ' . $user->full_phone_number . ', ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}
