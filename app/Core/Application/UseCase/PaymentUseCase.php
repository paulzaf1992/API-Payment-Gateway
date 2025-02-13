<?php

declare(strict_types=1);

namespace App\Core\Application\UseCase;

use App\Core\Domain\Repository\MerchantRepositoryInterface;
use App\Infrastructure\PSP\PaymentServiceInterface;

class PaymentUseCase
{
    private MerchantRepositoryInterface $merchantRepo;
    /** @var array<string,PaymentServiceInterface> */
    private array $pspServices;

    public function __construct(MerchantRepositoryInterface $merchantRepo, array $pspServices)
    {
        $this->merchantRepo = $merchantRepo;
        $this->pspServices  = $pspServices;
    }

    /**
     * @param string $authToken
     * @param float  $amount
     * @param array{card_number:string,expiration_date:string,cvv:string,cardholder_name:string} $cardData
     */
    public function charge(string $authToken, float $amount, array $cardData): array
    {
        $merchant = $this->merchantRepo->findByAuthToken($authToken);
        if (!$merchant) {
            throw new \RuntimeException('Merchant not found or invalid auth token');
        }

        $pspName = $merchant->getPsp();
        if (!isset($this->pspServices[$pspName])) {
            throw new \RuntimeException("PSP '{$pspName}' is not supported.");
        }

        $pspService = $this->pspServices[$pspName];
        return $pspService->charge($amount, $cardData, $merchant);
    }
}
