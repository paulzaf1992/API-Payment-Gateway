<?php

declare(strict_types=1);

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once __DIR__ . '/../../../vendor/autoload.php';

$entityPaths = [__DIR__ . '/../../Core/Domain/Entity'];
$isDevMode   = true;

// Use attribute mapping
$config = Setup::createAttributeMetadataConfiguration($entityPaths, $isDevMode);

$dbParams = [
    'driver'   => 'pdo_mysql',
    'host'     => $_ENV['DB_HOST']     ?? 'mariadb',
    'user'     => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? 'secret',
    'dbname'   => $_ENV['DB_DATABASE'] ?? 'payments_db',
    'charset'  => 'utf8mb4',
];

$entityManager = EntityManager::create($dbParams, $config);

return $entityManager;
