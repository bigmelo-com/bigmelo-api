<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserMessagesLimitControllerTest
 *
 * Run these specific tests
 * php artisan test tests/Feature/Http/Controllers/api/v1/UserMessagesLimitControllerTest.php
 *
 * @package Tests\Feature\Http\Controllers\api\v1
 */
class UserMessagesLimitControllerTest extends TestApi
{
    /**
     * User messages limit api endpoint
     */
    const ENDPOINT_USER_MESSAGES_LIMIT = '/v1/user/USERID/messages-limit';

    /**
     * Endpoint for a specific user
     *
     * @param int $user_id
     *
     * @return string
     */
    private function getEndpoint(int $user_id): string
    {
        return str_replace('USERID', $user_id, self::ENDPOINT_USER_MESSAGES_LIMIT);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_store_a_messages_limit_for_an_user(): void
    {
        Event::fake();

        $full_phone_number = explode('-', $this->faker->numerify('+##-###-###-####'));

        $country_code = $full_phone_number[0];
        $full_phone_number = implode('', $full_phone_number);
        $phone_number = str_replace($country_code, '', $full_phone_number);

        $user_data = [
            'role'              => 'user',
            'name'              => $this->faker->name,
            'last_name'         => $this->faker->lastName,
            'email'             => $this->faker->email,
            'country_code'      => $country_code,
            'phone_number'      => $phone_number,
            'full_phone_number' => $full_phone_number,
            'password'          => Hash::make('qwerty123')
        ];

        $user = User::create($user_data);

        $messages_limit = [
            'limit'  => 100
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('POST', $this->getEndpoint($user->id), $messages_limit);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'User messages limit has been stored successfully.');

        $user->refresh();

        $this->assertTrue($user->hasAvailableMessages());
        $this->assertEquals(100, $user->currentMessagesLimit()->available);
    }

    /**
     * @test
     *
     * @return void
     */
    public function unauthorized_user_can_not_set_messages_limit(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->faker->word())
            ->json('POST', $this->getEndpoint(1), []);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Unauthenticated.');
    }

}
