<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Class AdminDashboardControllerTest
 *
 * Run these specific tests
 * php artisan test tests/Feature/Http/Controllers/api/v1/AdminDashboardControllerTest.php
 *
 * @package Tests\Feature\Http\Controllers\api\v1
 */
class AdminDashboardControllerTest extends TestApi
{
    /**
     * Admin dashboard api endpoint
     */
    const ENDPOINT_ADMIN_DASHBOARD = '/v1/admin-dashboard';

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_get_total_leads_created_today(): void
    {
        $lead1 = Lead::create([
            'country_code'      => '+57',
            'phone_number'      => '3133920001',
            'full_phone_number' => '+573133920001'
        ]);

        $lead2 = Lead::create([
            'country_code'      => '+57',
            'phone_number'      => '3133920002',
            'full_phone_number' => '+573133920002',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('GET', self::ENDPOINT_ADMIN_DASHBOARD . '/daily-totals');

        $response->assertJsonPath('data.new_leads', 3);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_get_total_users_created_today(): void
    {
        $user1 = User::create([
            'role'              => 'user',
            'name'              => $this->faker->name,
            'email'             => $this->faker->email,
            'country_code'      => '+57',
            'phone_number'      => '3133920001',
            'full_phone_number' => '+573133920001',
            'password'          => Hash::make($this->faker->password)
        ]);

        $user2 = User::create([
            'role'              => 'user',
            'name'              => $this->faker->name,
            'email'             => $this->faker->email,
            'country_code'      => '+57',
            'phone_number'      => '3133920002',
            'full_phone_number' => '+573133920002',
            'password'          => Hash::make($this->faker->password)
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('GET', self::ENDPOINT_ADMIN_DASHBOARD . '/daily-totals');

        $response->assertJsonPath('data.new_leads', 1);
        $response->assertJsonPath('data.new_users', 3);
    }
}
