<?php

namespace Tests\Unit\Classes\Message;

use App\Classes\Message\ChatGPTMessageResponse;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Class ChatGPTMessageResponseTest
 *
 * Run these specific tests
 * php artisan test tests/Unit/Classes/Message/ChatGPTMessageResponseTest.php
 *
 * @package Tests\Unit\Classes\MercadoPago
 */
class ChatGPTMessageResponseTest extends TestCase
{
    private $faker;

    public function setUp(): void
    {
        $this->faker = Factory::create();
    }

    /**
     * @test
     */
    public function get_content_from_message_object(): void
    {
        $content = $this->faker->text(500);

        $message = new ChatGPTMessageResponse(content: $content);

        $this->assertEquals($content, $message->getContent());
    }

    /**
     * @test
     */
    public function get_chatgpt_id_from_message_object(): void
    {
        $content = $this->faker->text(500);
        $chatgpt_id = $this->faker->text(10);

        $message = new ChatGPTMessageResponse(content: $content, chatgpt_id: $chatgpt_id);

        $this->assertEquals($content, $message->getContent());
        $this->assertEquals($chatgpt_id, $message->getChatgptID());
    }

    /**
     * @test
     */
    public function get_object_type_from_message_object(): void
    {
        $content = $this->faker->text(500);
        $object_type = $this->faker->text(10);

        $message = new ChatGPTMessageResponse(content: $content, object_type: $object_type);

        $this->assertEquals($content, $message->getContent());
        $this->assertEquals($object_type, $message->getObjectType());
    }

    /**
     * @test
     */
    public function get_model_from_message_object(): void
    {
        $content = $this->faker->text(500);
        $model = $this->faker->text(10);

        $message = new ChatGPTMessageResponse(content: $content, model: $model);

        $this->assertEquals($content, $message->getContent());
        $this->assertEquals($model, $message->getModel());
    }

    /**
     * @test
     */
    public function get_role_from_message_object(): void
    {
        $content = $this->faker->text(500);
        $role = $this->faker->text(10);

        $message = new ChatGPTMessageResponse(content: $content, role: $role);

        $this->assertEquals($content, $message->getContent());
        $this->assertEquals($role, $message->getRole());
    }

    /**
     * @test
     */
    public function get_prompt_tokens_from_message_object(): void
    {
        $content = $this->faker->text(500);
        $prompt_tokens = $this->faker->numberBetween(1, 200);

        $message = new ChatGPTMessageResponse(content: $content, prompt_tokens: $prompt_tokens);

        $this->assertEquals($content, $message->getContent());
        $this->assertEquals($prompt_tokens, $message->getPromptTokens());
    }

    /**
     * @test
     */
    public function get_completion_tokens_from_message_object(): void
    {
        $content = $this->faker->text(500);
        $completion_tokens = $this->faker->numberBetween(1, 200);

        $message = new ChatGPTMessageResponse(content: $content, completion_tokens: $completion_tokens);

        $this->assertEquals($content, $message->getContent());
        $this->assertEquals($completion_tokens, $message->getCompletionTokens());
    }

    /**
     * @test
     */
    public function get_total_tokens_from_message_object(): void
    {
        $content = $this->faker->text(500);
        $total_tokens = $this->faker->numberBetween(1, 200);

        $message = new ChatGPTMessageResponse(content: $content, total_tokens: $total_tokens);

        $this->assertEquals($content, $message->getContent());
        $this->assertEquals($total_tokens, $message->getTotalTokens());
    }

}
