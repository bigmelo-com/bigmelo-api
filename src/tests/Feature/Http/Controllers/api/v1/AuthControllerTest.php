<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use http\Client\Response;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

/**
 * Class AuthControllerTest
 *
 * Run these specific tests
 * php artisan test tests/Feature/Http/Controllers/api/v1/AuthControllerTest.php
 *
 * @package Tests\Feature\Http\Controllers\api\v1
 */
class AuthControllerTest extends TestApi
{
    /**
     * Auth api endpoint
     */
    const ENDPOINT_AUTH = '/v1/auth';

    /**
     * @test
     *
     * @return void
     */
    public function get_access_token_with_email_and_password(): void
    {
        $response = $this->json('post', self::ENDPOINT_AUTH . '/get-token', [
            'email' => 'admin@mydomain.com',
            'password' => 'qwerty123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token']);
    }

    /**
     * @test
     *
     * @return void
     */
    public function login_error_wrong_credentials(): void
    {
        $response = $this->json('post', '/v1/auth/get-token', [
            'email' => 'wrong_email@mydomain.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Your email or password are incorrect.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function user_do_successful_signup(): void
    {
        $response = $this->json('post', '/v1/auth/signup', [
            'name' => 'User',
            'last_name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'country_code' => '+57',
            'phone_number' => '3248972647',
            'full_phone_number' => '+573248972647',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token', 'user']);
        $response->assertJsonPath('user.name', 'User');
        $response->assertJsonPath('user.last_name', 'Test');
        $response->assertJsonPath('user.email', 'test@example.com');
        $response->assertJsonPath('user.country_code', '+57');
        $response->assertJsonPath('user.phone_number', '3248972647');
        $response->assertJsonPath('user.full_phone_number', '+573248972647');
    }

}
