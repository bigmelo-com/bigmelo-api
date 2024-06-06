<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use App\Events\User\UserValidated;
use App\Models\Lead;
use App\Models\Message;
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

    /**
     *
     * @test
     * @return void
     */
    public function admin_can_get_total_number_of_messages_from_whatsapp_created_today(): void
    {
        $user1 = User::create([
            'role'              => 'user',
            'name'              => 'User 1',
            'email'             => 'user1@mydomain.com',
            'country_code'      => '+57',
            'phone_number'      => '3133920001',
            'full_phone_number' => '+573133920001',
            'password'          => Hash::make('qwerty123')
        ]);

        $user2 = User::create([
            'role'              => 'user',
            'name'              => 'User 2',
            'email'             => 'user2@mydomain.com',
            'country_code'      => '+57',
            'phone_number'      => '3133920001',
            'full_phone_number' => '+573133920002',
            'password'          => Hash::make('qwerty123')
        ]);

        $lead1 = Lead::create([
            'user_id'           => $user1->id,
            'first_name'        => $user1->name,
            'last_name'         => $user1->last_name,
            'email'             => $user1->email,
            'country_code'      => $user1->country_code,
            'phone_number'      => $user1->phone_number,
            'full_phone_number' => $user1->full_phone_number,
        ]);

        $lead2 = Lead::create([
            'user_id'           => $user2->id,
            'first_name'        => $user2->name,
            'last_name'         => $user2->last_name,
            'email'             => $user2->email,
            'country_code'      => $user2->country_code,
            'phone_number'      => $user2->phone_number,
            'full_phone_number' => $user2->full_phone_number,
        ]);

        $message1 = Message::create([
            'lead_id' => $lead1->id,
            'content' => $this->faker->text(300),
            'source'  => 'WhatsApp'
        ]);
        $message2 = Message::create([
            'lead_id' => $lead1->id,
            'content' => $this->faker->text(300),
            'source'  => 'Admin'
        ]);
        $message3 = Message::create([
            'lead_id' => $lead1->id,
            'content' => $this->faker->text(300),
            'source'  => 'WhatsApp'
        ]);
        $message4 = Message::create([
            'lead_id' => $lead1->id,
            'content' => $this->faker->text(300),
            'source'  => 'ChatGPT'
        ]);
        $message5 = Message::create([
            'lead_id' => $lead2->id,
            'content' => $this->faker->text(300),
            'source'  => 'WhatsApp'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('GET', self::ENDPOINT_ADMIN_DASHBOARD . '/daily-totals');

        $response->assertJsonPath('data.new_leads', 3);
        $response->assertJsonPath('data.new_users', 3);
        $response->assertJsonPath('data.new_whatsapp_messages', 3);
        $response->assertJsonPath('data.new_messages', 5);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_not_get_totals_with_a_wrong_date_format()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('GET', self::ENDPOINT_ADMIN_DASHBOARD . '/daily-totals?date=2024-01');

        $response->assertStatus(400);
        $response->assertJsonPath('message', 'Wrong date format. It have to be YYYY-MM-DD.');
    }

    /**
     *
     * @test
     * @return void
     */
    public function admin_can_get_totals_from_any_date(): void
    {
        $user1 = User::create([
            'role'              => 'user',
            'name'              => 'User 1',
            'email'             => 'user1@mydomain.com',
            'country_code'      => '+57',
            'phone_number'      => '3133920001',
            'full_phone_number' => '+573133920001',
            'password'          => Hash::make('qwerty123')
        ]);

        $user2 = User::create([
            'role'              => 'user',
            'name'              => 'User 2',
            'email'             => 'user2@mydomain.com',
            'country_code'      => '+57',
            'phone_number'      => '3133920001',
            'full_phone_number' => '+573133920002',
            'password'          => Hash::make('qwerty123')
        ]);

        $lead1 = Lead::create([
            'user_id'           => $user1->id,
            'first_name'        => $user1->name,
            'last_name'         => $user1->last_name,
            'email'             => $user1->email,
            'country_code'      => $user1->country_code,
            'phone_number'      => $user1->phone_number,
            'full_phone_number' => $user1->full_phone_number,
        ]);

        $lead2 = Lead::create([
            'user_id'           => $user2->id,
            'first_name'        => $user2->name,
            'last_name'         => $user2->last_name,
            'email'             => $user2->email,
            'country_code'      => $user2->country_code,
            'phone_number'      => $user2->phone_number,
            'full_phone_number' => $user2->full_phone_number,
        ]);

        $message1 = Message::create([
            'lead_id' => $lead1->id,
            'content' => $this->faker->text(300),
            'source'  => 'WhatsApp'
        ]);
        $message2 = Message::create([
            'lead_id' => $lead1->id,
            'content' => $this->faker->text(300),
            'source'  => 'Admin'
        ]);
        $message3 = Message::create([
            'lead_id' => $lead1->id,
            'content' => $this->faker->text(300),
            'source'  => 'WhatsApp'
        ]);
        $message4 = Message::create([
            'lead_id' => $lead1->id,
            'content' => $this->faker->text(300),
            'source'  => 'ChatGPT'
        ]);
        $message5 = Message::create([
            'lead_id' => $lead2->id,
            'content' => $this->faker->text(300),
            'source'  => 'WhatsApp'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('GET', self::ENDPOINT_ADMIN_DASHBOARD . '/daily-totals?date=2024-01-01');

        $response->assertJsonPath('data.new_leads', 0);
        $response->assertJsonPath('data.new_users', 0);
        $response->assertJsonPath('data.new_whatsapp_messages', 0);
        $response->assertJsonPath('data.new_messages', 0);
    }

    /**
     * @test
     *
     * @return void
     */
    public function unauthenticated_user_can_not_get_totals()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->faker->word())
            ->json('GET', self::ENDPOINT_ADMIN_DASHBOARD . '/daily-totals');

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Unauthenticated.');
    }

}
