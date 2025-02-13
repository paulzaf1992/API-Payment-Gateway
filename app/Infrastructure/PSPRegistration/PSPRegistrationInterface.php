<?php

declare(strict_types=1);

namespace App\Infrastructure\PSPRegistration;

interface PSPRegistrationInterface
{
    /**
     * @param array<string,mixed> $registrationData
     * @return string The PSP API key or secret
     */
    public function registerMerchant(array $registrationData): string;
}
