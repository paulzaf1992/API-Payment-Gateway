<?php

declare(strict_types=1);

namespace App\Core\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="merchants")
 */
#[ORM\Entity]
#[ORM\Table(name: 'merchants')]
class Merchant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $psp;

    #[ORM\Column(type: 'string', length: 255)]
    private string $pspApiKey;

    #[ORM\Column(type: 'string', length: 255)]
    private string $authToken;

    public function __construct(
        ?int $id,
        string $psp,
        string $pspApiKey,
        string $authToken
    ) {
        $this->id = $id;
        $this->psp = $psp;
        $this->pspApiKey = $pspApiKey;
        $this->authToken = $authToken;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getPsp(): string
    {
        return $this->psp;
    }

    public function setPsp(string $psp): void
    {
        $this->psp = $psp;
    }

    public function getPspApiKey(): string
    {
        return $this->pspApiKey;
    }

    public function setPspApiKey(string $pspApiKey): void
    {
        $this->pspApiKey = $pspApiKey;
    }

    public function getAuthToken(): string
    {
        return $this->authToken;
    }

    public function setAuthToken(string $authToken): void
    {
        $this->authToken = $authToken;
    }
}
