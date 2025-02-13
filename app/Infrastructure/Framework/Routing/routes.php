<?php

declare(strict_types=1);

use App\Infrastructure\Framework\Http\MerchantController;
use App\Infrastructure\Framework\Http\PaymentController;

return [
    [
        'method'  => 'POST',
        'path'    => '/merchant/create',
        'handler' => [MerchantController::class, 'createMerchant']
    ],
    [
        'method'  => 'POST',
        'path'    => '/merchant/update-psp',
        'handler' => [MerchantController::class, 'updatePsp']
    ],
    [
        'method'  => 'POST',
        'path'    => '/charge',
        'handler' => [PaymentController::class, 'charge']
    ],
];
