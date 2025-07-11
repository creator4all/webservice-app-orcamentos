<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

require __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

// Set up settings
$settings = require __DIR__ . '/config/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require __DIR__ . '/config/dependencies.php';
$dependencies($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();

// Register middleware
$middleware = require __DIR__ . '/config/middleware.php';
$middleware($app);

// Register routes
require_once __DIR__ . '/app/Routes.php';
\App\Routes::attachRoutes($app);

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(
    (bool)($_ENV['APP_DEBUG'] ?? false),
    true,
    true
);

// Set base path if defined in environment
$basePath = $_ENV['URL_BASE'] ?? '';
if (!empty($basePath)) {
    $app->setBasePath($basePath);
}

// Run App
$app->run();
