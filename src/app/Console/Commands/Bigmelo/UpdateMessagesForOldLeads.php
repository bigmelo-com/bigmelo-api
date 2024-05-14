<?php

namespace App\Console\Commands\Bigmelo;

use App\Events\Message\BigmeloMessageStored;
use App\Models\Lead;
use App\Models\Project;
use App\Repositories\MessageRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateMessagesForOldLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bigmelo:update-messages-for-old-leads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update messages for leads created older than one month ago';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $project = Project::find(1);
            $message_repository = new MessageRepository();

            $leads = Lead::where('created_at', '<', now()->subMonth())
            ->where('plan_id', null)
            ->where('remaining_messages', 0)
            ->get();

            foreach ($leads as $lead) {
                $lead->remaining_messages = config('bigmelo.message.registered_user_message_limit');
                $lead->save();

                $twilio_free_messages_template = "¡Hola {$lead->first_name}! 🌟 Bigmelo te da 3 mensajes de cortesía para que converses sobre cualquier tema con nuestra inteligencia artificial! ¿Sobre qué te gustaría conversar?";

                $message = $message_repository->storeMessage(
                    lead_id: $lead->id,
                    project_id: $project->id,
                    content: $twilio_free_messages_template,
                    source: 'Admin'
                );

                event(new BigmeloMessageStored($message));
            }

            echo "Old leads message limit updated \n";

            Log::info(
                'UpdateMessagesForOldLeads: Old leads message limit updated'
            );
        } catch (\Throwable $e) {
            Log::error(
                'UpdateMessagesForOldLeads: Internal error, ' .
                'Error: ' . $e->getMessage()
            );
        }
    }
}
