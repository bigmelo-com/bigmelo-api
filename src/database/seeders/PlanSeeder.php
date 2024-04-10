<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $lead = Lead::find(1);
            $project = $lead->projects()->first();
            $plan = Plan::where('name', 'Bigmelo')->first();

            if (!$plan) {
                $plan = Plan::create([
                    'project_id'    => $project->id,
                    'name'          => 'Bigmelo',
                    'description'   => 'Obten mensajes ilimitados durante un mes',
                    'price'         => 5.70,
                    'message_limit' => -1,
                    'period'        => '0d, 0w, 1m, 0y',
                ]);
            }

        } catch (\Throwable $e) {
            Log::info(get_class() . $e->getMessage());
        }
    }
}
