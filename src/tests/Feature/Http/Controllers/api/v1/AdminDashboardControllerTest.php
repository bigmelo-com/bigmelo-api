<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
}
