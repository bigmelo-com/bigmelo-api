<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use App\Models\Lead;
use App\Models\User;
use http\Client\Response;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
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

    /**
     * @test
     *
     * @return void
     */
    public function registered_user_request_recovery_link(): void
    {

        $data = [
            'email' => 'admin@mydomain.com'
        ];

        $response = $this->json('post', '/v1/auth/password-recovery', $data);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Recovery link has been seent');

    }

    /**
     * @test
     *
     * @return void
     */
    public function not_registered_user_can_not_request_recovery_link(): void
    {

        $data = [
            'email' => 'falseUser@mydomain.com'
        ];

        $response = $this->json('post', '/v1/auth/password-recovery', $data);

        $response->assertStatus(404);
        $response->assertJsonPath('message', 'Email not linked to any user');

    }

    /**
     * @test
     *
     * @return void
     */
    public function user_can_reset_password_succesfully(): void
    {

        $user = User::create([
            'role'              => 'forgotten',
            'name'              => 'User',
            'last_name'         => 'Test',
            'email'             => 'forgottenUser@test.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test')
        ]);

        $data = [
            'password'              => 'resetPassword',
            'password_confirmation' => 'resetPassword'
        ];

        $token = $user->createToken('recovery-token', $user->getRoleAbilities());

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->json('post', '/v1/auth/reset-password', $data);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Password updated succesfully');

    }

    /**
     * @test
     *
     * @return void
     */
    public function user_reset_password_failed_not_authorized_user(): void
    {

        $user = User::create([
            'role'              => 'user',
            'name'              => 'User',
            'last_name'         => 'Test',
            'email'             => 'forgottenUser@test.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test')
        ]);

        $data = [
            'password'              => 'resetPassword',
            'password_confirmation' => 'resetPassword'
        ];

        $token = $user->createToken('recovery-token', $user->getRoleAbilities());

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->json('post', '/v1/auth/reset-password', $data);

        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Not authorized');

    }

    /**
     * @test
     *
     * @return void
     */
    public function user_reset_password_failed_invalid_token(): void
    {

        $data = [
            'password'              => 'resetPassword',
            'password_confirmation' => 'resetPassword'
        ];

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . 'false-toke')
            ->json('post', '/v1/auth/reset-password', $data);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Unauthenticated.');

    }

    /**
     * @test
     *
     * @return void
     */
    public function user_reset_password_failed_due_not_confirmed_password(): void
    {

        $user = User::create([
            'role'              => 'forgotten',
            'name'              => 'User',
            'last_name'         => 'Test',
            'email'             => 'forgottenUser@test.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test')
        ]);

        $data = [
            'password'              => 'resetPassword',
            'password_confirmation' => 'wrongConfirmation'
        ];

        $token = $user->createToken('recovery-token', $user->getRoleAbilities());

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->json('post', '/v1/auth/reset-password', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => []]);
        $response->assertJsonPath('errors.password', ['The password field confirmation does not match.']);

    }
}
