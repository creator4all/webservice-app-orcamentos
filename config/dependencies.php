<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Views\PhpRenderer;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        ResponseFactoryInterface::class => function () {
            return new ResponseFactory();
        },
        
        // Database PDO connection
        PDO::class => function (ContainerInterface $c) {
            $settings = $c->get('settings')['db'];
            
            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $settings['driver'],
                $settings['host'],
                $settings['port'],
                $settings['database'],
                $settings['charset']
            );
            
            $pdo = new PDO(
                $dsn,
                $settings['username'],
                $settings['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            
            return $pdo;
        },
        
        // PHP View Renderer
        PhpRenderer::class => function (ContainerInterface $c) {
            return new PhpRenderer(__DIR__ . '/../templates');
        },
    ]);
};
