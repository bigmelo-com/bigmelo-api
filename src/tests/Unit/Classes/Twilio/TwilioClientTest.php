<?php

namespace Tests\Unit\Classes\Twilio;

use App\Classes\ChatGPT\ChatGPTClient;
use App\Classes\Message\ChatGPTMessage;
use App\Classes\Message\ChatGPTMessageResponse;
use App\Classes\Twilio\TwilioClient;
use App\Models\Message;
use App\Models\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Class TwilioClientTest
 *
 * Run these specific tests
 * php artisan test tests/Unit/Classes/Twilio/TwilioClientTest.php
 *
 * @package Tests\Unit\Classes\Message
 */
class TwilioClientTest extends TestCase
{

    /**
     * @test
     */
    public function get_file_content(): void
    {
//        $file_url = 'https://api.twilio.com/Media/MEeed77c79931868d622e7d4774adc77e2';
//
//        $twilio_client = new TwilioClient('1');
//        $file_content = $twilio_client->getFileContent($file_url);
//
//        $filename = 'audio_' . time() . '.ogg';
//
//        Storage::disk('public')->put($filename, $file_content);
//
//        $chatgpt_client = new ChatGPTClient();
//        $response = $chatgpt_client->getTextFromAudioFile(storage_path('app/public/' . $filename));
//
//        print_r($response); die;

        $this->assertTrue(true);
    }

}
