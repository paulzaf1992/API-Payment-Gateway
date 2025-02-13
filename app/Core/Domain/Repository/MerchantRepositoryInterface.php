<?php

declare(strict_types=1);

namespace App\Core\Domain\Repository;

use App\Core\Domain\Entity\Merchant;

interface MerchantRepositoryInterface
{
    public function findById(int $id): ?Merchant;

    public function findByAuthToken(string $authToken): ?Merchant;

    public function create(Merchant $merchant): Merchant;

    public function save(Merchant $merchant): void;
}
