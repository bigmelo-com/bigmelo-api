<?php

namespace App\Console\Commands\Bigmelo;

use App\Events\Message\BigmeloMessageStored;
use App\Models\Project;
use App\Repositories\MessageRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AssignFreeMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bigmelo:assign-free-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign free messages on first of each month to all lead without messages in the bigmelo default plan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $default_project = Project::find(1); 
        $message_repository = new MessageRepository();

        try {
            $leads = $default_project->leads()->where('remaining_messages', 0)->where('plan_id', null)->get();

            foreach ($leads as $lead) {
                $message = $message_repository->storeMessage(
                    lead_id: $lead->id,
                    project_id: 1,
                    content: "Hola $lead->first_name, tienes 5 mensajes gratis para que converses con la inteligencia artificial de Bigmelo. ¿Qué te gustaria preguntar?",
                    source: 'Admin'
                );

                event(new BigmeloMessageStored($message));

                $lead->remaining_messages = $default_project->message_limit;
                $lead->save();
            }

            Log::info(
                'Assigned 5 messages to free accounts with zero messages, ' .
                'default_project_id: ' . $default_project . ', '
            );

        } catch (\Throwable $e) {
            Log::error(
                'AssignFreeMessages: Internal error, ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}
