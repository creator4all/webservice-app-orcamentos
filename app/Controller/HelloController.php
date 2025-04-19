<?php
declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

final class HelloController extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function hello(Request $request, Response $response, array $args): Response
    {
        return $this->encapsular_response(function (Request $request, Response $response, array $args) {
            return [
                'status' => 'success',
                'data' => [
                    'message' => 'Hello World'
                ]
            ];
        }, $request, $response, $args);
    }
}
