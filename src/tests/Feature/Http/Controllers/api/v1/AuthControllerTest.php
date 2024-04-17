<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use App\Models\Lead;
use App\Models\User;
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
        $response->assertJsonStructure(['access_token']);
    }
    
    /**
     * @test
     *
     * @return void
     */
    public function user_signup_error_invalid_data(): void
    {

        $invalid_data = [
            'name'                  => '', 
            'last_name'             => '', 
            'email'                 => 'wrongEmail', 
            'password'              => 'short',
            'password_confirmation' => 'different', 
            'country_code'          => '',
            'phone_number'          => '', 
            'full_phone_number'     => '', 
        ];

        $response = $this->json('post', '/v1/auth/signup', $invalid_data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => []]);
        $response->assertJsonPath('errors.name', ['The name field is required.']);
        $response->assertJsonPath('errors.last_name', ['The last name field is required.']);
        $response->assertJsonPath('errors.email', ['The email field must be a valid email address.']);
        $response->assertJsonPath('errors.password', [
            'The password field must be at least 8 characters.',
            'The password field confirmation does not match.'
        ]);
        $response->assertJsonPath('errors.country_code', ['The country code field is required.']);
        $response->assertJsonPath('errors.phone_number', ['The phone number field is required.']);
        $response->assertJsonPath('errors.full_phone_number', ['The full phone number field is required.']);

    }


}
