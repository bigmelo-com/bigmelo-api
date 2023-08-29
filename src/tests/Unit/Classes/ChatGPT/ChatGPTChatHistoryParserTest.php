<?php

namespace Tests\Unit\Classes\ChatGPT;

use App\Classes\ChatGPT\ChatGPTChatHistoryParser;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Class ChatGPTChatHistoryParserTest
 *
 * Run these specific tests
 * php artisan test tests/Unit/Classes/ChatGPT/ChatGPTChatHistoryParserTest.php
 *
 * @package Tests\Unit\Models
 */
class ChatGPTChatHistoryParserTest extends TestCase
{
    /**
     * @test
     */
    public function get_good_formated_chat_history_for_chatgpt(): void
    {
        $faker = Factory::create();

        $messages = [
            [
                'id' => 3,
                'user_id' => 1,
                'content' => $faker->text(200),
                'source' => 'ChatGPT',
                'created_at' => '2023-08-29T00:55:45.000000Z',
                'updated_at' => '2023-08-29T00:55:45.000000Z',
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'content' => $faker->text(200),
                'source' => 'API',
                'created_at' => '2023-08-29T00:55:39.000000Z',
                'updated_at' => '2023-08-29T00:55:39.000000Z',
            ],
            [
                'id' => 1,
                'user_id' => 1,
                'content' => $faker->text(200),
                'source' => 'ChatGPT',
                'created_at' => '2023-08-29T00:55:39.000000Z',
                'updated_at' => '2023-08-29T00:55:39.000000Z',
            ]
        ];

        $new_message = $faker->text(200);

        $result_expected = [
            ['role' => 'assistant', 'content' => $messages[2]['content']],
            ['role' => 'user', 'content' => $messages[1]['content']],
            ['role' => 'assistant', 'content' => $messages[0]['content']],
            ['role' => 'user', 'content' => $new_message]
        ];

        $chat_history_parser = new ChatGPTChatHistoryParser($messages, $new_message);
        $result = $chat_history_parser->getChatHistory();

        $this->assertEquals($result_expected, $result);
    }
}
