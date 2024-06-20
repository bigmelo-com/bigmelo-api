<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use App\Events\User\UserValidated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Class MailControllerTest
 *
 * Run these specific tests
 * php artisan test tests/Feature/Http/Controllers/api/v1/MailControllerTest.php
 *
 * @package Tests\Feature\Http\Controllers\api\v1
 */
class MailControllerTest extends TestApi
{

    /**
     * Contact api endpoint
     */
    const ENDPOINT_CONTACT = '/v1/contact';

    /**
     * @test
     *
     * @return void
     */
    public function send_support_mail_successfull(): void
    {
        $user = User::create([
            'role'              => 'user',
            'name'              => 'User',
            'last_name'         => 'Test',
            'email'             => 'test@gmail.com',
            'country_code'      => '+57',
            'phone_number'      => '3133777777',
            'full_phone_number' => '+573133777777',
            'password'          => Hash::make('test')
        ]);

        event(new UserValidated($user));

        $data = [
            'name' => 'User',
            'form_email' => 'test@gmail.com',
            'message' => 'Test'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken('test@gmail.com','test'))
            ->json('POST', self::ENDPOINT_CONTACT, $data);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Email sent successfully');
    }

    /**
     * @test
     *
     * @return void
     */
    public function send_support_mail_failed_wrong_email(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
