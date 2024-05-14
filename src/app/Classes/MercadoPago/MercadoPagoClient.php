<?php

namespace App\Classes\MercadoPago;

use App\Exceptions\MercadoPago\MercadoPagoCouldNotCreatePreference;
use App\Exceptions\MercadoPago\MercadoPagoCouldNotGetPaymentInfo;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Resources\Payment;
use MercadoPago\Resources\Preference;

class MercadoPagoClient
{
    /**
     * @var PreferenceClient
     */
    private PreferenceClient $client;

    public function __construct()
    {
        $token = config('services.mercadopago.token');
        
        MercadoPagoConfig::setAccessToken($token);
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

        $this->client = new PreferenceClient();
    }

    /**
     * Create preference
     *
     * @return Preference
     *
     * @throws MercadoPagoCouldNotCreatePreference
     */
    public function createPreference($request): Preference
    {
        try {

            $preference = $this->client->create($request);

            return $preference;

        } catch (\Throwable $e) {
            throw new MercadoPagoCouldNotCreatePreference(
                'Error Mercado Pago Client, ' .
                'error: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get payment from Mercado Pago
     *
     * @return Preference
     *
     * @throws MercadoPagoCouldNotCreatePreference
     */
    public function getPaymentFromMercadoPago($payment_id): Payment
    {
        try {
            $payment_client = new PaymentClient();

            return $payment_client->get($payment_id);
        } catch (\Throwable $e) {
            throw new MercadoPagoCouldNotGetPaymentInfo(
                'Error Mercado Pago Client, ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}