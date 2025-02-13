<?php

declare(strict_types=1);

namespace App\Infrastructure\PSP;

use App\Core\Domain\Entity\Merchant;

interface PaymentServiceInterface
{
    public function charge(float $amount, array $cardData, Merchant $merchant): array;
}
