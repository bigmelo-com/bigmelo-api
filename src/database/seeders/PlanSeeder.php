<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                    'description'   => 'First plan.',
                    'price'         => 0,
                    'message_limit' => 20,
                    'period'        => '1d, 1w, 1m, 1y',
                ]);
            }

        } catch (\Throwable $e) {
            Log::info(get_class() . $e->getMessage());
        }
    }
}
