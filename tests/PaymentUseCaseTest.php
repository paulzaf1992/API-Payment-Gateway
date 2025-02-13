<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Core\Application\UseCase\PaymentUseCase;
use App\Core\Domain\Entity\Merchant;
use App\Core\Domain\Repository\MerchantRepositoryInterface;
use App\Infrastructure\PSP\PaymentServiceInterface;

final class PaymentUseCaseTest extends TestCase
{
    public function testChargeWithValidMerchantReturnsSuccess(): void
    {
        $mockMerchant = new Merchant(1, 'stripe', 'sk_test_mock', 'valid_token');

        $repo = $this->createMock(MerchantRepositoryInterface::class);
        // Return the mock merchant if the token is "valid_token"
        $repo->method('findByAuthToken')
             ->willReturn($mockMerchant);

        // Mock the PSP service to return a success response
        $mockPsp = $this->createMock(PaymentServiceInterface::class);
        $mockPsp->method('charge')->willReturn([
            'psp' => 'stripe',
            'status' => 'success',
            'transaction_id' => 'txn_123',
            'amount_charged' => 50.0
        ]);

        $useCase = new PaymentUseCase($repo, ['stripe' => $mockPsp]);

        $result = $useCase->charge('valid_token', 50.0, [
            'card_number' => '4242424242424242',
            'expiration_date' => '12/25',
            'cvv' => '123',
            'cardholder_name' => 'John Doe'
        ]);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('stripe', $result['psp']);
        $this->assertEquals(50.0, $result['amount_charged']);
    }

    public function testChargeWithInvalidTokenThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Merchant not found or invalid auth token');

        $repo = $this->createMock(MerchantRepositoryInterface::class);
        // Return null => no merchant found
        $repo->method('findByAuthToken')->willReturn(null);

        $useCase = new PaymentUseCase($repo, []);
        // This should throw
        $useCase->charge('bogus_token', 10.0, []);
    }
}
