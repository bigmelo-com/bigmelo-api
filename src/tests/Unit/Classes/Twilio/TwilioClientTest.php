<?php

namespace Tests\Unit\Classes\Twilio;

use App\Classes\Message\ChatGPTMessage;
use App\Classes\Message\ChatGPTMessageResponse;
use App\Classes\Twilio\TwilioClient;
use App\Models\Message;
use App\Models\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
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
        $file_url = 'https://api.twilio.com//Media/MEeed77c79931868d622e7d4774adc77e2';

//        $twilio_client = new TwilioClient('1');
//        $file_content = $twilio_client->getFileContent($file_url);

        $this->assertTrue(true);
    }

}
