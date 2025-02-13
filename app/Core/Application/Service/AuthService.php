<?php

declare(strict_types=1);

namespace App\Core\Application\Service;

use App\Core\Domain\Entity\Merchant;
use App\Core\Domain\Repository\MerchantRepositoryInterface;

class AuthService
{
    private MerchantRepositoryInterface $merchantRepo;

    public function __construct(MerchantRepositoryInterface $merchantRepo)
    {
        $this->merchantRepo = $merchantRepo;
    }

    /**
     * Parse a "Bearer <token>" header or return null if invalid.
     */
    public function authenticateMerchant(?string $header): ?Merchant
    {
        if (!$header) {
            return null;
        }

        // Expect "Bearer <token>"
        $parts = explode(' ', $header);
        if (count($parts) !== 2 || strtolower($parts[0]) !== 'bearer') {
            return null;
        }
        $token = $parts[1];

        return $this->merchantRepo->findByAuthToken($token);
    }
}
