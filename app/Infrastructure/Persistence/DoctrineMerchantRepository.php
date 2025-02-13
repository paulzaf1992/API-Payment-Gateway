<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Core\Domain\Entity\Merchant;
use App\Core\Domain\Repository\MerchantRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class DoctrineMerchantRepository implements MerchantRepositoryInterface
{
    private EntityManagerInterface $em;
    private EntityRepository $repo;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em   = $em;
        $this->repo = $em->getRepository(Merchant::class);
    }

    public function findById(int $id): ?Merchant
    {
        return $this->repo->find($id);
    }

    public function findByAuthToken(string $authToken): ?Merchant
    {
        return $this->repo->findOneBy(['authToken' => $authToken]);
    }

    public function create(Merchant $merchant): Merchant
    {
        $this->em->persist($merchant);
        $this->em->flush();
        return $merchant;
    }

    public function save(Merchant $merchant): void
    {
        $this->em->persist($merchant);
        $this->em->flush();
    }
}
