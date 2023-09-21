<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $user = User::find(1);
            $organization = Organization::where('name', 'Bigmelo')->first();

            if (!$organization) {
                $organization = Organization::create([
                    'owner_id'      => $user->id,
                    'name'          => 'Bigmelo',
                    'description'   => 'Initial organization.',
                ]);

                $user->organizations()->attach($organization);
            }

            if ($organization->projects->count() === 0) {
                $project = Project::create([
                    'organization_id'       => $organization->id,
                    'name'                  => 'Bigmelo',
                    'description'           => 'Project base.',
                    'phone_number'          => '+14155238886',
                    'assistant_description' => 'a nice Bigmelo chatbot powering by AI',
                    'assistant_goal'        => 'to help all people to answer their questions and doubts',
                    'language'              => 'Spanish',
                    'default_answer'        => 'https://bigmelo.com',
                    'has_system_prompt'     => false
                ]);

                $user->lead->projects()->attach($project);
            }

        } catch (\Throwable $e) {
            Log::info(get_class() . $e->getMessage());
        }
    }
}
