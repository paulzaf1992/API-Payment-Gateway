<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Core\Application\Service\AuthService;
use App\Core\Domain\Entity\Merchant;
use App\Core\Domain\Repository\MerchantRepositoryInterface;

final class AuthServiceTest extends TestCase
{
    public function testAuthenticateMerchantWithBearerToken(): void
    {
        // Mock a Merchant
        $mockMerchant = new Merchant(null, 'stripe', 'sk_test_mock', 'random_hex_token');
        $mockMerchant->setId(123);

        // Mock Repo to return that merchant if token is "valid_hex"
        $repo = $this->createMock(MerchantRepositoryInterface::class);
        $repo->method('findByAuthToken')
             ->with('valid_hex')
             ->willReturn($mockMerchant);

        $authService = new AuthService($repo);

        // We pass a valid bearer header
        $header = 'Bearer valid_hex';
        $result = $authService->authenticateMerchant($header);

        $this->assertNotNull($result);
        $this->assertEquals(123, $result->getId());
        $this->assertEquals('random_hex_token', $result->getAuthToken());
    }

    public function testAuthenticateMerchantWithInvalidHeaderReturnsNull(): void
    {
        $repo = $this->createMock(MerchantRepositoryInterface::class);
        // Should never call findByAuthToken
        $repo->expects($this->never())->method('findByAuthToken');

        $authService = new AuthService($repo);

        // Wrong format
        $header = 'NotBearerOrSomethingElse';
        $result = $authService->authenticateMerchant($header);

        $this->assertNull($result);
    }
}
