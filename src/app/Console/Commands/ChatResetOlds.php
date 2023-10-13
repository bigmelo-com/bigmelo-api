<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ChatResetOlds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:reset-olds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset old chats by lead and project';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        echo "Resetting old chats. \n";

        $query = "
            UPDATE 	chats
            SET 	active = FALSE
            WHERE 	id IN (
                SELECT 	id
                FROM 	(
                    SELECT 		C.id, C.lead_id, C.project_id, C.active, MAX(M.created_at) AS last_message
                    FROM 		chats C
                    INNER JOIN 	messages M
                    ON 			M.chat_id = C.id
                    WHERE 		c.active = TRUE
                    GROUP BY 	C.id, C.lead_id, C.project_id, C.active
                ) AS  	CHATS
            WHERE 	last_message <= NOW() - INTERVAL '4 hours'
            );
        ";

        $data = DB::select($query);
        $chats_updated = count($data);

        echo "$chats_updated chats were reseted. \n";
    }
}
