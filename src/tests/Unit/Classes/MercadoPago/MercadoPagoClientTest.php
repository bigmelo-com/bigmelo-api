<?php

namespace Tests\Unit\Classes\MercadoPago;

use App\Classes\MercadoPago\MercadoPagoClient;
use MercadoPago\Resources\Preference;
use PHPUnit\Framework\TestCase;

/**
 * Class MercadoPagoClientTest
 *
 * Run these specific tests
 * php artisan test tests/Unit/Classes/MercadoPago/MercadoPagoClientTest.php
 *
 * @package Tests\Unit\Classes\Message
 */
class MercadoPagoClientTest extends TestCase
{
    private $mercado_pago_client;

    public function setUp(): void
    {
        $this->mercado_pago_client = $this->createMock(MercadoPagoClient::class);
    }

    /**
     * @test
     */
    public function test_success_to_create_preference(){
        $preference = $this->mercado_pago_client->createPreference([
            "items" => [
                [
                    "title" => "test",
                    "quantity" => 1,
                    "unit_price" => 5.70,
                    "currency_id" => "USD"
                ]
            ]
        ]);

        $this->mercado_pago_client
            ->method('createPreference')
            ->willReturn($preference);
        
        $this->assertInstanceOf(Preference::class, $preference);
    }
}
