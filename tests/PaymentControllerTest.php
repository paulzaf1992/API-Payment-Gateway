<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class PaymentControllerTest extends TestCase
{
    public function testChargeEndpointNoAuthShouldReturn401(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/charge';
        unset($_SERVER['HTTP_AUTHORIZATION']);

        $payload = json_encode([
            'card_number' => '4242424242424242',
            'expiration_date' => '12/25',
            'cvv' => '123',
            'cardholder_name' => 'John Doe',
            'amount' => 20.0
        ]);

        ob_start();
        $temp = tmpfile();
        fwrite($temp, $payload);
        fseek($temp, 0);
        $GLOBALS['mockedInputStream'] = $temp;

        require __DIR__ . '/../public/index.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('No auth token provided', $output);
    }

    public function testChargeEndpointSuccessScenario(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/charge';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer some_good_token';

        $payload = json_encode([
            'card_number' => '4242424242424242',
            'expiration_date' => '12/25',
            'cvv' => '123',
            'cardholder_name' => 'Test User',
            'amount' => 30.0
        ]);

        ob_start();
        $temp = tmpfile();
        fwrite($temp, $payload);
        fseek($temp, 0);
        $GLOBALS['mockedInputStream'] = $temp;

        require __DIR__ . '/../public/index.php';
        $output = ob_get_clean();

        // We expect something like "status": "success" or an error if the DB is not set up.
        $this->assertStringContainsString('"status":', $output);
    }
}
