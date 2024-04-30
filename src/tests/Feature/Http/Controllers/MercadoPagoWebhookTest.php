<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Transaction;
use Tests\Feature\Http\Controllers\api\v1\TestApi;

/**
 * Class MercadoPagoWebhookTest
 *
 * Run these specific tests
 * php artisan test tests/Feature/Http/Controllers/MercadoPagoWebhookTest.php
 *
 * @package Tests\Feature\Http\Controllers
 */
class MercadoPagoWebhookTest extends TestApi
{
    /**
     * Mercado pago webhook endpoint
     */
    const ENDPOINT_PLAN = '/webhook/mercado-pago/payment-webhook';

    /**
     * @test
     *
     * @return void
     */
    public function payment_webhook_received_succesfully(): void
    {
        Transaction::create([
            'preference_id' => 'test',
            'lead_id'       => 1,
            'plan_id'       => 1,
            'amount'        => '5.70'
        ]);

        $webhook_data = [
            'data' => [
                'id' => '12345'
            ]
        ];

        $response = $this->json('POST', self::ENDPOINT_PLAN, $webhook_data);

        $response->assertStatus(200);
    }

}
