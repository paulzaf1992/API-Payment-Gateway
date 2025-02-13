<?php

declare(strict_types=1);

namespace App\Infrastructure\Framework\Http;

use App\Core\Application\UseCase\PaymentUseCase;
use App\Infrastructure\Persistence\DoctrineMerchantRepository;
use App\Infrastructure\PSP\PinPaymentService;
use App\Infrastructure\PSP\StripePaymentService;
use Doctrine\ORM\EntityManagerInterface;

class PaymentController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Authenticated endpoint: charge a card.
     * Expects "Authorization: Bearer <token>"
     * Expects JSON with card_number, expiration_date, cvv, cardholder_name, amount
     */
    public function charge(): void
    {
        header('Content-Type: application/json');

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!isset($data['card_number'], $data['expiration_date'], $data['cvv'], $data['cardholder_name'], $data['amount'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        // Prepare PaymentUseCase
        $repo = new DoctrineMerchantRepository($this->em);
        $pspServices = [
            'stripe'       => new StripePaymentService(),
            'pin_payments' => new PinPaymentService()
        ];
        $useCase = new PaymentUseCase($repo, $pspServices);

        // Auth token from header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        // If not "Bearer <token>", the useCase->charge(...) will fail if token not found in DB

        $amount = (float) $data['amount'];
        $cardData = [
            'card_number'     => $data['card_number'],
            'expiration_date' => $data['expiration_date'],
            'cvv'             => $data['cvv'],
            'cardholder_name' => $data['cardholder_name']
        ];

        try {
            $result = $useCase->charge($authHeader, $amount, $cardData);
            http_response_code(200);
            echo json_encode($result);
        } catch (\RuntimeException $ex) {
            http_response_code(400);
            echo json_encode(['error' => $ex->getMessage()]);
        } catch (\Exception $ex) {
            http_response_code(500);
            echo json_encode(['error' => $ex->getMessage()]);
        }
    }
}
