<?php
declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

final class HomeController extends Controller
{
    
    public function index(Request $request, Response $response): Response
    {
        return $this->encapsular_response(function (Request $request, Response $response, array $args) {
            $data = [
                'app_name' => 'Orcamentos API',
                'version' => '1.0.0',
                'documentation' => '/docs',
            ];
    
            // $response->getBody()->write(json_encode($data));
            // return $response->withHeader('Content-Type', 'application/json');
            return ['status' => 'success', 'data' => $data];
        }, $request, $response, $args);
    }
}
