<?php

namespace App\Services\PaymentGateway;

interface PaymentGatewayInterface
{
    /**
     * Get the gateway identifier code.
     */
    public function getGatewayCode(): string;

    /**
     * Check if the gateway is configured and active.
     */
    public function isConfigured(): bool;
}
