<?php

declare(strict_types=1);

namespace App\Infrastructure\Framework\Http;

use App\Core\Application\Service\AuthService;
use App\Core\Domain\Entity\Merchant;
use App\Infrastructure\Persistence\DoctrineMerchantRepository;
use Doctrine\ORM\EntityManagerInterface;

class MerchantController
{
    private EntityManagerInterface $em;
    private AuthService $authService;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        // We'll build an AuthService that uses the same Repo
        $merchantRepo = new DoctrineMerchantRepository($this->em);
        $this->authService = new AuthService($merchantRepo);
    }

    /**
     * Public endpoint: create a Merchant with random 16-hex authToken.
     * Accepts JSON like:
     * {
     *   "psp": "stripe",
     *   "psp_api_key": "sk_test_xxx"
     * }
     */
    public function createMerchant(): void
    {
        header('Content-Type: application/json');

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (empty($data['psp']) || empty($data['registration_data']['user_stripe_key'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing psp or psp_api_key']);
            return;
        }

        // Generate a random 16-hex
        $authToken = bin2hex(random_bytes(8));

        // Create a new Merchant entity
        $merchant = new Merchant(
            null, // new
            $data['psp'],
            $data['psp_api_key'],
            $authToken
        );

        $repo = new DoctrineMerchantRepository($this->em);
        $repo->create($merchant);

        http_response_code(201);
        echo json_encode([
            'message' => 'Merchant created',
            'merchant_id' => $merchant->getId(),
            'auth_token' => $authToken
        ]);
    }

    /**
     * Authenticated endpoint: update PSP for the authenticated merchant.
     * Accepts JSON:
     * {
     *   "merchant_id": 12,
     *   "new_psp": "pin_payments"
     * }
     *
     * Requires "Authorization: Bearer <token>" header
     */
    public function updatePsp(): void
    {
        header('Content-Type: application/json');

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (empty($data['merchant_id']) || empty($data['new_psp'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing merchant_id or new_psp']);
            return;
        }

        // Authenticate
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        $merchantAuth = $this->authService->authenticateMerchant($authHeader);
        if (!$merchantAuth) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        // Ensure the merchant being updated is the same as the authenticated one
        $merchantId = (int)$data['merchant_id'];
        if ($merchantAuth->getId() !== $merchantId) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden: cannot update another merchant']);
            return;
        }

        // Update
        $repo = new DoctrineMerchantRepository($this->em);
        $merchant = $repo->findById($merchantId);
        if (!$merchant) {
            http_response_code(404);
            echo json_encode(['error' => 'Merchant not found']);
            return;
        }

        $merchant->setPsp($data['new_psp']);
        $repo->save($merchant);

        http_response_code(200);
        echo json_encode([
            'message' => 'PSP updated',
            'merchant_id' => $merchant->getId(),
            'psp' => $merchant->getPsp()
        ]);
    }
}
