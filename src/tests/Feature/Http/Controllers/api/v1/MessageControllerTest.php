<?php

namespace Tests\Feature\Http\Controllers\api\v1;

use http\Client\Response;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

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
    public function store_a_new_message(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getToken())
            ->json('POST', self::ENDPOINT_MESSAGE, [
                'message' => $this->faker->text(300)
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message']);
    }
}
