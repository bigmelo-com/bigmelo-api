<?php

namespace Tests\Unit\Classes\Message;

use App\Classes\Message\ChatGPTMessage;
use App\Classes\Message\ChatGPTMessageResponse;
use App\Models\Message;
use App\Models\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Class ChatGPTMessageTest
 *
 * Run these specific tests
 * php artisan test tests/Unit/Classes/Message/ChatGPTMessageTest.php
 *
 * @package Tests\Unit\Classes\Message
 */
class ChatGPTMessageTest extends TestCase
{
    use RefreshDatabase;

    private $faker;

    private $user;

    private $chatgpt_message;

    private $fields = [];

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate');
        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);

        $this->faker = Factory::create();

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
            'password'          => '$2y$10$dmQmyyu./5uEb.Ti/ZeO3e80V8.mbivA4K1b43O9yvjWbvff0J7qK'
        ];

        $this->user = User::create($user_data);

        $content = $this->faker->text(500);
        $chatgpt_id = $this->faker->text(10);
        $object_type = $this->faker->text(10);
        $model = $this->faker->text(10);
        $role = $this->faker->text(10);
        $prompt_tokens = $this->faker->numberBetween(1, 200);
        $completion_tokens = $this->faker->numberBetween(1, 200);
        $total_tokens = $this->faker->numberBetween(1, 200);

        $chat_gpt_message_response = new ChatGPTMessageResponse(
            content: $content,
            chatgpt_id: $chatgpt_id,
            object_type: $object_type,
            model: $model,
            role: $role,
            prompt_tokens: $prompt_tokens,
            completion_tokens: $completion_tokens,
            total_tokens: $total_tokens
        );

        $this->chat_gpt_message = new ChatGPTMessage($this->user->id, $chat_gpt_message_response);

        $this->chat_gpt_message->save();

        $this->fields = [
            'content' => $content,
            'chatgpt_id' => $chatgpt_id,
            'object_type' => $object_type,
            'model' => $model,
            'role' => $role,
            'prompt_tokens' => $prompt_tokens,
            'completion_tokens' => $completion_tokens,
            'total_tokens' => $total_tokens
        ];
    }

    /**
     * @test
     */
    public function save_new_message_test_content(): void
    {
        $message = Message::find($this->chat_gpt_message->getMessage()->id);

        $this->assertEquals($this->fields['content'], $message->content);
    }

    /**
     * @test
     */
    public function save_new_message_test_data_from_chatgpt(): void
    {
        $message = Message::find($this->chat_gpt_message->getMessage()->id);

        $this->assertEquals($this->fields['chatgpt_id'], $message->chatgpt_message->chatgpt_id);
        $this->assertEquals($this->fields['object_type'], $message->chatgpt_message->object_type);
        $this->assertEquals($this->fields['model'], $message->chatgpt_message->model);
        $this->assertEquals($this->fields['role'], $message->chatgpt_message->role);
        $this->assertEquals($this->fields['prompt_tokens'], $message->chatgpt_message->prompt_tokens);
        $this->assertEquals($this->fields['completion_tokens'], $message->chatgpt_message->completion_tokens);
        $this->assertEquals($this->fields['total_tokens'], $message->chatgpt_message->total_tokens);
    }

}
