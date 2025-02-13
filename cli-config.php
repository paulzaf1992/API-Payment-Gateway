<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

// 1) Load Composer autoload
require_once __DIR__ . '/vendor/autoload.php';

// 2) Retrieve the EntityManager from your existing bootstrap code
$entityManager = require __DIR__ . '/app/Infrastructure/Persistence/doctrine.php';

// 3) Return the HelperSet
return ConsoleRunner::createHelperSet($entityManager);
