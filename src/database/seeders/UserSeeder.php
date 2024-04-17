<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserSeeder extends Seeder
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

            if (!$user) {
                $user = User::create([
                    'role'              => 'admin',
                    'name'              => 'Admin',
                    'email'             => 'admin@mydomain.com',
                    'country_code'      => '+57',
                    'phone_number'      => '3133929826',
                    'full_phone_number' => '+573133929826',
                    'password'          => Hash::make('qwerty123'),
                ]);
            }

            if (!$user->lead) {
                $lead = Lead::create([
                    'user_id'           => $user->id,
                    'first_name'        => $user->name,
                    'last_name'         => $user->last_name,
                    'email'             => $user->email,
                    'country_code'      => $user->country_code,
                    'phone_number'      => $user->phone_number,
                    'full_phone_number' => $user->full_phone_number,
                ]);
            }

            $user = User::find(2);

            if (!$user) {
                $user = User::create([
                    'role'              => 'admin',
                    'name'              => 'Abel',
                    'email'             => 'moreno.abel@gmail.com',
                    'country_code'      => '+57',
                    'phone_number'      => '3133929826',
                    'full_phone_number' => '+573133929826',
                    'password'          => Hash::make('qwerty123')
                ]);
            }

            if (!$user->lead) {
                $lead = Lead::create([
                    'user_id'           => $user->id,
                    'first_name'        => $user->name,
                    'last_name'         => $user->last_name,
                    'email'             => $user->email,
                    'country_code'      => $user->country_code,
                    'phone_number'      => $user->phone_number,
                    'full_phone_number' => $user->full_phone_number,
                ]);
            }

        } catch (\Throwable $e) {
            Log::info(get_class() . $e->getMessage());
        }
    }
}
