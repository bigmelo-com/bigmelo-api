<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use App\Events\Message\MessageStored;
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
    const ENDPOINT_MESSAGE = '/api/v1/message';

    /**
     * @test
     *
     * @return void
     */
    public function user_can_store_a_new_message(): void
    {
        Event::fake();

        $message = $this->faker->text(300);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('POST', self::ENDPOINT_MESSAGE, [
                'message' => $message
            ]);

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
}
