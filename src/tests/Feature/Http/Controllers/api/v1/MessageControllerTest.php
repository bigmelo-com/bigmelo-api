<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use App\Models\Message;
use Illuminate\Support\Facades\Event;

/**
 * Class MessageControllerTest
 *
 * Run these specific tests
 * php artisan test tests/Feature/Http/Controllers/api/v1/MessageControllerTest.php
 *
 * @package Tests\Feature\Http\Controllers\api\v1
 */
class MessageControllerTest extends TestApi
{
    /**
     * Message api endpoint
     */
    const ENDPOINT_MESSAGE = '/v1/message';

    /**
     * @test
     *
     * @return void
     */
    public function user_can_store_a_new_message(): void
    {
        Event::fake();

        $message_data = [
            'message' => $this->faker->text(300),
            'source'  => 'API',
            'user_id' => 1
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('POST', self::ENDPOINT_MESSAGE, $message_data);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message']);
    }

    /**
     * @test
     *
     * @return void
     */
    public function user_get_error_if_try_to_store_an_empty_message(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('POST', self::ENDPOINT_MESSAGE, ['message' => '']);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['message']]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_store_a_new_message(): void
    {
        Event::fake();

        $message_data = [
            'message' => $this->faker->text(300),
            'source'  => 'Admin',
            'user_id' => 1
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('POST', self::ENDPOINT_MESSAGE, $message_data);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Message has been stored successfully.');
    }

    /**
     * @test
     *
     * @return void
     */
    public function admin_can_list_all_message_by_user_id(): void
    {
        $message1 = Message::create([
            'user_id' => 1,
            'content' => $this->faker->text(300),
            'source'  => 'API'
        ]);
        $message2 = Message::create([
            'user_id' => 1,
            'content' => $this->faker->text(300),
            'source'  => 'ChatGPT'
        ]);
        $message3 = Message::create([
            'user_id' => 1,
            'content' => $this->faker->text(300),
            'source'  => 'API'
        ]);
        $message4 = Message::create([
            'user_id' => 2,
            'content' => $this->faker->text(300),
            'source'  => 'ChatGPT'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('GET', self::ENDPOINT_MESSAGE . '?user_id=1');

        $response_data = json_decode($response->getContent())->data;

        $response->assertStatus(200);
        $this->assertCount(3, $response_data);
    }

    /**
     * @test
     *
     * @return void
     */
    public function unauthorized_user_can_not_list_messages()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->faker->word())
            ->json('GET', self::ENDPOINT_MESSAGE . '?user_id=1');

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Unauthenticated.');
    }
}
