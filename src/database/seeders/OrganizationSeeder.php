<?php

namespace Database\Seeders;

use App\Models\Organization;
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
            Organization::create([
                'owner_id'      => 1,
                'name'          => 'Bigmelo',
                'description'   => 'Initial organization.',
            ]);

        } catch (\Throwable $e) {
            Log::info(get_class() . $e->getMessage());
        }
    }
}
