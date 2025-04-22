<?php
declare(strict_types=1);

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Controller\HomeController;
use App\Controller\HelloController;

return function (App $app) {
    // Home route
    $app->get('/', HomeController::class . ':index');
    
    // API routes
    $app->group('/api', function (Group $group) {
        // Auth routes
        $group->group('/auth', function (Group $group) {
            $group->post('/login', 'App\Controller\AuthController:login');
            $group->post('/register', 'App\Controller\AuthController:register');
        });
        
        // User routes
        $group->group('/users', function (Group $group) {
            $group->get('', 'App\Controller\UserController:index');
            $group->get('/{id}', 'App\Controller\UserController:show');
            $group->post('', 'App\Controller\UserController:create');
            $group->put('/{id}', 'App\Controller\UserController:update');
            $group->delete('/{id}', 'App\Controller\UserController:delete');
        });
        
        // Hello World route
        $group->get('/hello', HelloController::class . ':hello');
        
        $group->post('/register/{cnpj}', 'App\Controller\UsuarioController:cadastrar');
        $group->post('/signin', 'App\Controller\UsuarioController:signin');
        
        $group->post('/parceiros', 'App\Controller\ParceiroController:cadastrar');
    });
    
    // Documentation route for Swagger
    $app->get('/docs', function ($request, $response) {
        return $response->withHeader('Location', '/docs/index.html')->withStatus(302);
    });
};
