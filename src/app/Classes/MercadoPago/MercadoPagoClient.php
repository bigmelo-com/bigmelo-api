<?php

namespace App\Classes\MercadoPago;

use App\Exceptions\MercadoPago\MercadoPagoCouldNotCreatePreference;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
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
}
