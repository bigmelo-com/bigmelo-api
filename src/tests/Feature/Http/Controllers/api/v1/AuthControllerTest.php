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
}
