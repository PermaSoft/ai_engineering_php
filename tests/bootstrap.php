<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG'] ?? false) {
    umask(0000);
}

// Ensure test database exists and is fresh
$testDbPath = dirname(__DIR__).'/var/data_test.db';
if (file_exists($testDbPath)) {
    @unlink($testDbPath);
}

// Run database migrations for test environment
passthru(sprintf(
    'php "%s/bin/console" doctrine:schema:create --env=test --no-interaction --quiet 2>&1',
    dirname(__DIR__)
));

// Load fixtures for tests
passthru(sprintf(
    'php "%s/bin/console" doctrine:fixtures:load --env=test --no-interaction --quiet 2>&1',
    dirname(__DIR__)
));
