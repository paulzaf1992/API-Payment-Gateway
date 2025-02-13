<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class MerchantControllerTest extends TestCase
{
    public function testCreateMerchantSuccess(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/merchant/create';
        unset($_SERVER['HTTP_AUTHORIZATION']);

        $payload = json_encode([
            'psp' => 'stripe',
            'psp_api_key' => 'sk_test_example'
        ]);

        ob_start();
        $temp = tmpfile();
        fwrite($temp, $payload);
        fseek($temp, 0);
        $GLOBALS['mockedInputStream'] = $temp;

        require __DIR__ . '/../public/index.php';
        $output = ob_get_clean();

        // We expect "merchant created" and an auth_token
        $this->assertStringContainsString('"merchant_id":', $output);
        $this->assertStringContainsString('"auth_token":"', $output);
    }

    public function testUpdatePspWithoutAuthShouldReturn401(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/merchant/update-psp';
        unset($_SERVER['HTTP_AUTHORIZATION']);

        $payload = json_encode([
            'merchant_id' => 1,
            'new_psp' => 'pin_payments'
        ]);

        ob_start();
        $temp = tmpfile();
        fwrite($temp, $payload);
        fseek($temp, 0);
        $GLOBALS['mockedInputStream'] = $temp;

        require __DIR__ . '/../public/index.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Unauthorized', $output);
    }

    public function testUpdatePspWithAuthButMismatchedMerchantIdShouldReturn403(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/merchant/update-psp';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer some_token'; // Suppose merchant is ID=2, but we pass ID=1

        $payload = json_encode([
            'merchant_id' => 1,
            'new_psp' => 'stripe'
        ]);

        ob_start();
        $temp = tmpfile();
        fwrite($temp, $payload);
        fseek($temp, 0);
        $GLOBALS['mockedInputStream'] = $temp;

        require __DIR__ . '/../public/index.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Forbidden', $output);
    }

    public function testUpdatePspSuccess(): void
    {
        // For a full integration test, you'd need a real DB record. 
        // But let's just check the "200" path. This is partial.

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/merchant/update-psp';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer correct_token';

        $payload = json_encode([
            'merchant_id' => 5,
            'new_psp' => 'pin_payments'
        ]);

        ob_start();
        $temp = tmpfile();
        fwrite($temp, $payload);
        fseek($temp, 0);
        $GLOBALS['mockedInputStream'] = $temp;

        require __DIR__ . '/../public/index.php';
        $output = ob_get_clean();

        // In a real test, we'd confirm the DB changed if we had a test DB set up.
        // We'll just look for "PSP updated" in the output.
        $this->assertStringContainsString('"message":"PSP updated"', $output);
    }
}
