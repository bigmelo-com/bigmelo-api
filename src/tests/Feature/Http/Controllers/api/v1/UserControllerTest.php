<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserControllerTest
 *
 * Run these specific tests
 * php artisan test tests/Feature/Http/Controllers/api/v1/UserControllerTest.php
 *
 * @package Tests\Feature\Http\Controllers\api\v1
 */
class UserControllerTest extends TestApi
{
    /**
     * Message api endpoint
     */
    const ENDPOINT_USER = '/v1/user';

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_store_a_new_user(): void
    {
        Event::fake();

        $full_phone_number = explode('-', $this->faker->numerify('+##-###-###-####'));

        $country_code = $full_phone_number[0];
        $full_phone_number = implode('', $full_phone_number);
        $phone_number = str_replace($country_code, '', $full_phone_number);

        $user_data = [
            'name'              => $this->faker->name,
            'email'             => $this->faker->email,
            'country_code'      => $country_code,
            'phone_number'      => $phone_number,
            'full_phone_number' => $full_phone_number
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('POST', self::ENDPOINT_USER, $user_data);

        $response_content = json_decode($response->getContent());

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'User has been stored successfully.');

        $user = User::find($response_content->user_id);
        $this->assertEquals($full_phone_number, $user->full_phone_number);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_not_store_a_new_user_if_email_exist(): void
    {
        Event::fake();

        $full_phone_number = explode('-', $this->faker->phoneNumber);

        $country_code = $full_phone_number[0];
        $full_phone_number = implode('', $full_phone_number);
        $phone_number = str_replace($country_code, '', $full_phone_number);

        $user_data = [
            'name'              => $this->faker->name,
            'email'             => 'admin@mydomain.com',
            'country_code'      => $country_code,
            'phone_number'      => $phone_number,
            'full_phone_number' => $full_phone_number
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('POST', self::ENDPOINT_USER, $user_data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['email']]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_not_store_a_new_user_with_invalid_email(): void
    {
        Event::fake();

        $full_phone_number = explode('-', $this->faker->phoneNumber);

        $country_code = $full_phone_number[0];
        $full_phone_number = implode('', $full_phone_number);
        $phone_number = str_replace($country_code, '', $full_phone_number);

        $user_data = [
            'name'              => $this->faker->name,
            'email'             => $this->faker->name,
            'country_code'      => $country_code,
            'phone_number'      => $phone_number,
            'full_phone_number' => $full_phone_number
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('POST', self::ENDPOINT_USER, $user_data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['email']]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_not_store_a_new_user_with_invalid_full_phone_number(): void
    {
        Event::fake();

        $full_phone_number = explode('-', $this->faker->phoneNumber);

        $country_code = $full_phone_number[0];
        $full_phone_number = implode('', $full_phone_number);
        $phone_number = str_replace($country_code, '', $full_phone_number);

        $user_data = [
            'name'              => $this->faker->name,
            'email'             => $this->faker->email,
            'country_code'      => $country_code,
            'phone_number'      => $phone_number,
            'full_phone_number' => $phone_number
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('POST', self::ENDPOINT_USER, $user_data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['full_phone_number']]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function unauthorized_user_can_not_create_users()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->faker->word())
            ->json('POST', self::ENDPOINT_USER, []);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Unauthenticated.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function inactive_user_validation_successfully()
    {
        Event::fake();

        User::create([
            'role'              => 'inactive',
            'active'            => false,
            'name'              => 'User',
            'last_name'         => 'Test',
            'email'             => 'user@test.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test'),
            'validation_code'   => '123456'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken('user@test.com','test'))
            ->json('POST', self::ENDPOINT_USER . '/validate', ['validation_code' => '123456']);

            $response->assertStatus(200);
            $response->assertJsonPath('message', 'User has been validated successfully.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function inactive_user_validation_code_is_invalid()
    {
        Event::fake();

        User::create([
            'role'              => 'inactive',
            'active'            => false,
            'name'              => 'User',
            'last_name'         => 'Test',
            'email'             => 'user@test.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test'),
            'validation_code'   => '123456'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken('user@test.com','test'))
            ->json('POST', self::ENDPOINT_USER . '/validate', ['validation_code' => '000000']);

        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Invalid code.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function not_inactive_user_can_not_validate_code()
    {
        Event::fake();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('POST', self::ENDPOINT_USER . '/validate', ['validation_code' => '000000']);

        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Not Authorized.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function inactive_user_create_validation_code_successfully()
    {
        User::create([
            'role'              => 'inactive',
            'active'            => false,
            'name'              => 'User',
            'last_name'         => 'Test',
            'email'             => 'user@test.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test')
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken('user@test.com','test'))
            ->json('PATCH', self::ENDPOINT_USER . '/validation-code', []);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'A new validation code was created successfully.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function unauthorized_user_create_validation_code_failed()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('PATCH', self::ENDPOINT_USER . '/validation-code', []);

        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Not Authorized.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function user_update_information_successfully()
    {

        $new_data = [
            'name' => 'newName',
            'last_name' => 'newLastName',
            'country_code' => '+57',
            'phone_number' => '3451237890',
            'full_phone_number' => '+573451237890',
            'email' => 'newMail@test.com',
            'new_password' => 'newPasswordTest',
            'new_password_confirmation' => 'newPasswordTest',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('PATCH', self::ENDPOINT_USER . '/update-user', $new_data);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'User updated successfully');
    }

    /**
     * @test
     *
     * @return void
     */
    public function unauthenticated_user_update_information_failed()
    {

        $new_data = [
            'name' => 'newName',
            'last_name' => 'newLastName',
            'country_code' => '+57',
            'phone_number' => '3451237890',
            'full_phone_number' => '+573451237890',
            'email' => 'newMail@test.com',
            'new_password' => 'newPasswordTest',
            'new_password_confirmation' => 'newPasswordTest',
        ];

        $response = $this->withHeader('Authorization', 'Bearer NotValidToken')
            ->json('PATCH', self::ENDPOINT_USER . '/update-user', $new_data);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Unauthenticated.');
    }

}
