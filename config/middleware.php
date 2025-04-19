<?php
declare(strict_types=1);

use Slim\App;
use Tuupola\Middleware\JwtAuthentication;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as Psr7Response;

return function (App $app) {
    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();

    // CORS middleware
    $app->add(function (Request $request, RequestHandler $handler): Response {
        $response = $handler->handle($request);
        
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withAddedHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache');
    });

    // Options pre-flight requests
    $app->options('/{routes:.+}', function (Request $request, Response $response) {
        return $response;
    });

    // JWT Authentication middleware
    $app->add(new JwtAuthentication([
        'path' => ['/api'],
        'ignore' => ['/api/auth/login', '/api/auth/register', '/api/hello', '/api/signin'],
        'secret' => $_ENV['JWT_SECRET'] ?? 'your_jwt_secret_key',
        'algorithm' => $_ENV['JWT_ALGORITHM'] ?? 'HS256',
        'secure' => false, // Set to true in production
        'error' => function (Response $response, array $arguments) {
            $data = [
                'success' => false,
                'message' => $arguments['message'],
            ];
            $response = new Psr7Response();
            $response->getBody()->write(json_encode($data));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        },
        'before' => function (Request $request, array $arguments) {
            return $request->withAttribute('jwt', $arguments['decoded']);
        }
    ]));
};
