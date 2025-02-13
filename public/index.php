<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;

require_once __DIR__ . '/../vendor/autoload.php';

// 1) Create or fetch the Doctrine EntityManager
$entityManager = require __DIR__ . '/../app/Infrastructure/Persistence/doctrine.php';
assert($entityManager instanceof EntityManagerInterface);

// 2) Load the routes array
$routes = require __DIR__ . '/../app/Infrastructure/Framework/Routing/routes.php';

// 3) Extract request method + path
$method = $_SERVER['REQUEST_METHOD'];
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 4) Match against routes
$matched = false;
foreach ($routes as $route) {
    if ($route['method'] === $method && $route['path'] === $path) {
        [$className, $methodName] = $route['handler'];

        // Instantiate the controller
        $controller = new $className($entityManager);
        $controller->$methodName();

        $matched = true;
        break;
    }
}

// 5) If no route matched, return 404
if (!$matched) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not Found']);
}
