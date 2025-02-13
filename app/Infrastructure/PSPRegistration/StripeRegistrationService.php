<?php

declare(strict_types=1);

namespace App\Infrastructure\PSPRegistration;

class StripeRegistrationService implements PSPRegistrationInterface
{
    public function registerMerchant(array $registrationData): string
    {
        // Possibly do an OAuth flow or fetch user-provided key
        if (!empty($registrationData['user_stripe_key'])) {
            return $registrationData['user_stripe_key'];
        }
        // Mock fallback
        return 'sk_test_stripeKey_mocked';
    }
}
