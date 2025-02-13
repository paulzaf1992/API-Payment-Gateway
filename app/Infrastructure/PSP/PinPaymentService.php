<?php

declare(strict_types=1);

namespace App\Infrastructure\PSP;

use App\Core\Domain\Entity\Merchant;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class PinPaymentService implements PaymentServiceInterface
{
    private const BASE_URL = 'https://test-api.pinpayments.com';

    public function charge(float $amount, array $cardData, Merchant $merchant): array
    {
        $client = new Client([
            'base_uri' => self::BASE_URL,
            'auth'     => [$merchant->getPspApiKey(), ''],
            'headers'  => [ 'Content-Type' => 'application/json' ]
        ]);

        $payload = [
            'email' => 'customer@example.com',
            'description' => 'PinPayments charge example',
            'amount' => (int) round($amount * 100),
            'currency' => 'USD',
            'ip_address' => '127.0.0.1',
            'card' => [
                'number' => $cardData['card_number'],
                'expiry_month' => $this->extractExpMonth($cardData['expiration_date']),
                'expiry_year'  => $this->extractExpYear($cardData['expiration_date']),
                'cvc'          => $cardData['cvv'],
                'name'         => $cardData['cardholder_name'] ?? 'Unknown'
            ]
        ];

        try {
            $response = $client->post('/1/charges', ['json' => $payload]);
            $body = json_decode($response->getBody()->getContents(), true);
            if (!isset($body['response'])) {
                return [
                    'psp' => 'pin_payments',
                    'status' => 'failed',
                    'transaction_id' => null,
                    'amount_charged' => 0,
                    'message' => 'No response key from PinPayments'
                ];
            }
            $resp = $body['response'];
            if (!empty($resp['success'])) {
                return [
                    'psp' => 'pin_payments',
                    'status' => 'success',
                    'transaction_id' => $resp['token'] ?? null,
                    'amount_charged' => $amount,
                    'message' => null
                ];
            }
            return [
                'psp' => 'pin_payments',
                'status' => 'failed',
                'transaction_id' => $resp['token'] ?? null,
                'amount_charged' => 0,
                'message' => $resp['error_message'] ?? 'PinPayments error'
            ];
        } catch (GuzzleException $e) {
            return [
                'psp' => 'pin_payments',
                'status' => 'failed',
                'transaction_id' => null,
                'amount_charged' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    private function extractExpMonth(string $exp): int
    {
        $parts = explode('/', $exp);
        return (int)$parts[0];
    }

    private function extractExpYear(string $exp): int
    {
        $parts = explode('/', $exp);
        $year = (int)$parts[1];
        if ($year < 100) {
            $year += 2000;
        }
        return $year;
    }
}
