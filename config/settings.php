<?php
declare(strict_types=1);

use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        'settings' => [
            'displayErrorDetails' => (bool)($_ENV['APP_DEBUG'] ?? false),
            'logErrorDetails' => true,
            'logErrors' => true,
            'app' => [
                'name' => 'Orcamentos API',
                'url' => $_ENV['APP_URL'] ?? 'http://localhost:80',
                'env' => $_ENV['APP_ENV'] ?? 'development',
            ],
            'db' => [
                'driver' => $_ENV['DB_CONNECTION'] ?? 'mysql',
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'port' => $_ENV['DB_PORT'] ?? 3306,
                'database' => $_ENV['DB_DATABASE'] ?? 'orcamentos',
                'username' => $_ENV['DB_USERNAME'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ],
            'jwt' => [
                'secret' => $_ENV['JWT_SECRET'] ?? 'your_jwt_secret_key',
                'algorithm' => $_ENV['JWT_ALGORITHM'] ?? 'HS256',
                'expiration' => (int)($_ENV['JWT_EXPIRATION'] ?? 3600),
            ],
            'mail' => [
                'host' => $_ENV['MAIL_HOST'] ?? 'smtp.example.com',
                'port' => (int)($_ENV['MAIL_PORT'] ?? 587),
                'username' => $_ENV['MAIL_USERNAME'] ?? 'your_email@example.com',
                'password' => $_ENV['MAIL_PASSWORD'] ?? 'your_email_password',
                'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
                'from' => [
                    'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'your_email@example.com',
                    'name' => $_ENV['MAIL_FROM_NAME'] ?? 'Orcamentos App',
                ],
            ],
        ],
    ]);
};
