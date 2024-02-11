<?php

namespace App\Listeners\User;

use App\Classes\Twilio\TwilioClient;
use App\Events\Message\BigmeloMessageStored;
use App\Events\User\UserValidated;
use App\Models\Project;
use App\Repositories\MessageRepository;
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
    public function handle(UserValidated $event): void
    {
        $project = Project::find(1);
        $lead = $event->user_validated->lead;
        $message_repository = new MessageRepository();
        $twilio_welcome_template = "Hola {$event->user_validated->name}! 🌟 Bienvenido a Bigmelo! 🚀 Estamos aquí con el poder de la inteligencia artificial para ayudarte. ¿En qué puedo asistirte hoy? 😊";

        try {
            $message = $message_repository->storeMessage(
                lead_id: $lead->id,
                project_id: $project->id,
                content: $twilio_welcome_template,
                source: 'Admin'
            );

            event(new BigmeloMessageStored($message));

            Log::info(
                'Welcome message sent to whatsapp, ' .
                'from: ' . $project->phone_number . ', ' .
                'to: ' . $lead->full_phone_number . ', '
            );

        } catch (\Throwable $e) {
            Log::error(
                'SendWelcomeMessageToWhatsapp: Internal error, ' .
                'from: ' . $project->phone_number . ', ' .
                'to: ' . $lead->full_phone_number . ', ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}
