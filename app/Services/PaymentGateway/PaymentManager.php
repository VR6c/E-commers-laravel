<?php

namespace App\Services\PaymentGateway;

use App\Models\PaymentGateway;
use App\Services\PaymentGateway\ABAPayWayService;
use App\Services\PaymentGateway\PayPalService;
use App\Services\PaymentGateway\StripeService;
use InvalidArgumentException;

class PaymentManager
{
    /**
     * Create a payment service instance for the given gateway code.
     *
     * @throws \InvalidArgumentException
     */
    public static function make(string $gatewayCode, string $environment = 'sandbox'): PaymentGatewayInterface
    {
        // Check if gateway exists & active
        $gateway = PaymentGateway::where('code', $gatewayCode)
            ->where('is_active', true)
            ->first();

        if (! $gateway) {
            throw new InvalidArgumentException("Payment gateway [{$gatewayCode}] not found or inactive.");
        }

        // Map gateway codes to their service classes
        $services = [
            'paypal' => PayPalService::class,
            'stripe' => StripeService::class,
            'abapayway' => ABAPayWayService::class,
        ];

        if (! array_key_exists($gatewayCode, $services)) {
            throw new InvalidArgumentException("No service class found for [{$gatewayCode}].");
        }

        $serviceClass = $services[$gatewayCode];

        return new $serviceClass($environment);
    }
}
