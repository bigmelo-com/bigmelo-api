<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use App\Events\User\UserStored;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

/**
 * Class ProfileControllerTest
 *
 * Run these specific tests
 * php artisan test tests/Feature/Http/Controllers/api/v1/ProfileControllerTest.php
 *
 * @package Tests\Feature\Http\Controllers\api\v1
 */
class ProfileControllerTest extends TestApi
{
    /**
     * Profile api endpoint
     */
    const ENDPOINT_PROFILE = '/v1/profile';

    /**
     * @test
     *
     * @return void
     */
    public function user_get_profile_information_successfully()
    {
        $user = User::create([
            'role'              => 'user',
            'name'              => 'User',
            'last_name'         => 'Test',
            'email'             => 'test@test.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test')
        ]);

        event(new UserStored($user));

        $remaining_messages = $user->lead->remaining_messages;
        $message_limit = $user->lead->plan ? $user->lead->plan->message_limit : $user->lead->projects->first()->message_limit;
        $used_messages = $message_limit - $remaining_messages;

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken('test@test.com','test'))
            ->json('GET', self::ENDPOINT_PROFILE);

        $response->assertStatus(200);
        $response->assertJsonPath('data.first_name', $user->name);
        $response->assertJsonPath('data.last_name', $user->last_name);
        $response->assertJsonPath('data.phone_number', $user->phone_number);
        $response->assertJsonPath('data.email', 'test@test.com');
        $response->assertJsonPath('data.remaining_messages', $remaining_messages == -1 ? 'Ilimitado' : $remaining_messages);
        $response->assertJsonPath('data.message_limit', $message_limit == -1 ? 'Ilimitado' : $message_limit);
        $response->assertJsonPath('data.used_messages', $used_messages);
        
    }

    /**
     * @test
     *
     * @return void
     */
    public function unauthenticate_user_can_not_get_profile_information()
    {

        $response = $this->withHeader('Authorization', 'Bearer ')
            ->json('GET', self::ENDPOINT_PROFILE);

        $response->assertStatus(401);
        
    }

}
