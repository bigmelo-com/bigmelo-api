<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhooks Routes
|--------------------------------------------------------------------------
|
*/

Route::prefix('/whatsapp')->group(function() {
    Route::post('/message', 'App\Http\Controllers\webhooks\WhatsAppController@storeMessage');
});

Route::prefix('/mercado-pago')->group(function() {
    Route::post('/payment-webhook', 'App\Http\Controllers\webhooks\MercadoPagoController@paymentWebhook');
});
