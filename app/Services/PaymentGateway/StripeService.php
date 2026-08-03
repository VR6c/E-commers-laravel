<?php

namespace App\Services\PaymentGateway;

use App\Models\PaymentGateway;
use Stripe\StripeClient;

class StripeService implements PaymentGatewayInterface
{
    protected $environment;

    protected $client;

    protected $publicKey;

    public function __construct($environment = 'sandbox')
    {
        $this->environment = $environment;

        // Get Stripe gateway from database
        $gateway = PaymentGateway::where('code', 'stripe')->first();

        // Fetch keys from the gateway configs
        $secretKey = $gateway ? $gateway->getConfigValue('secret', $environment) : null;

        $this->publicKey = $gateway ? $gateway->getConfigValue('public', $environment) : null;

        if (! $secretKey) {
            throw new \Exception('Stripe secret key not found!');
        }

        // ✅ This is where you fix it: pass only the secret key string
        $this->client = new StripeClient($secretKey);
    }

    public function getPublicKey()
    {
        return $this->publicKey;
    }

    public function createPaymentIntent($amount, $currency = 'usd')
    {
        return $this->client->paymentIntents->create([
            'amount' => $amount,
            'currency' => $currency,
        ]);
    }

    public function getGatewayCode(): string
    {
        return 'stripe';
    }

    public function isConfigured(): bool
    {
        return !empty($this->client);
    }
}
