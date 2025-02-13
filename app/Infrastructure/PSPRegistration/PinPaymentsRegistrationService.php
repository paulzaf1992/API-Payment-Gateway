<?php

declare(strict_types=1);

namespace App\Infrastructure\PSPRegistration;

class PinPaymentsRegistrationService implements PSPRegistrationInterface
{
    public function registerMerchant(array $registrationData): string
    {
        if (!empty($registrationData['pin_api_key'])) {
            return $registrationData['pin_api_key'];
        }
        // Mock fallback
        return 'pin_mocked_key';
    }
}
