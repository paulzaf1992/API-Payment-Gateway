<?php

declare(strict_types=1);

namespace App\Infrastructure\PSP;

use App\Core\Domain\Entity\Merchant;
use Stripe\StripeClient;

class StripePaymentService implements PaymentServiceInterface
{
    public function charge(float $amount, array $cardData, Merchant $merchant): array
    {
        $stripe = new StripeClient($merchant->getPspApiKey());

        try {
            // Create PaymentMethod
            $paymentMethod = $stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'number'    => $cardData['card_number'],
                    'exp_month' => $this->extractExpMonth($cardData['expiration_date']),
                    'exp_year'  => $this->extractExpYear($cardData['expiration_date']),
                    'cvc'       => $cardData['cvv'],
                ],
                'billing_details' => [
                    'name' => $cardData['cardholder_name'] ?? 'Unknown'
                ]
            ]);

            // Create PaymentIntent
            $intent = $stripe->paymentIntents->create([
                'amount' => (int) round($amount * 100),
                'currency' => 'usd',
                'payment_method' => $paymentMethod->id,
                'confirmation_method' => 'automatic',
                'confirm' => true
            ]);

            if ($intent->status === 'succeeded') {
                return [
                    'psp' => 'stripe',
                    'status' => 'success',
                    'transaction_id' => $intent->id,
                    'amount_charged' => $amount,
                    'message' => null
                ];
            }
            $err = $intent->last_payment_error->message ?? 'Unknown error';
            return [
                'psp' => 'stripe',
                'status' => $intent->status,
                'transaction_id' => $intent->id,
                'amount_charged' => 0,
                'message' => $err
            ];
        } catch (\Exception $e) {
            return [
                'psp' => 'stripe',
                'status' => 'failed',
                'transaction_id' => null,
                'amount_charged' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    private function extractExpMonth(string $expirationDate): int
    {
        $parts = explode('/', $expirationDate);
        return (int)$parts[0];
    }

    private function extractExpYear(string $expirationDate): int
    {
        $parts = explode('/', $expirationDate);
        $year = (int)$parts[1];
        if ($year < 100) {
            $year += 2000;
        }
        return $year;
    }
}
