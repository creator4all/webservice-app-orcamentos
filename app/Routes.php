<?php
namespace App;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Controller\HomeController;
use App\Controller\HelloController;

class Routes
{
    public static function attachRoutes(App $app): void
    {
        // Home route
        $app->get('/', HomeController::class . ':index');
        
        // API routes
        $app->group('/api', function (Group $group) {
            // Hello World route
            $group->get('/hello', HelloController::class . ':hello');

            // Rotas públicas de usuário
            $group->post('/register/{cnpj}', 'App\Controller\UserController:register');
            $group->post('/signin', 'App\Controller\UserController:signIn');
            
            $group->post('/recover-password', 'App\Controller\PasswordResetController:recoverPassword');
            $group->post('/reset-password', 'App\Controller\PasswordResetController:resetPassword');
            
            $group->group('/parceiros', function (Group $group) {
                $group->get('', 'App\Controller\ParceiroController:listar');
                $group->post('', 'App\Controller\ParceiroController:cadastrar');
                $group->put('/{id}', 'App\Controller\ParceiroController:atualizarInformacoesDoParceiro');
            });
            
            // Auth routes
            $group->group('/auth', function (Group $group) {
                $group->post('/login', 'App\Controller\AuthController:login');
                $group->post('/register', 'App\Controller\AuthController:register');
            });
            
            // User routes
            $group->group('/users', function (Group $group) {
                $group->get('', 'App\Controller\UserController:index');
                $group->get('/gestores', 'App\Controller\UserController:listManagers');
                $group->get('/{id}', 'App\Controller\UserController:show');
                $group->post('', 'App\Controller\UserController:create');
                $group->put('/{id}', 'App\Controller\UserController:update');
                $group->delete('/{id}', 'App\Controller\UserController:delete');
                $group->post('/{partnerId}/manager', 'App\Controller\UserController:criarGerente');
            });
            
            // Protected user routes
            $group->put('/profile', 'App\Controller\UserController:editProfile');
            $group->put('/usuarios/perfil', 'App\Controller\UserController:editProfile');
        });
        
        // Documentation route for Swagger
        $app->get('/docs', function ($request, $response) {
            return $response->withHeader('Location', '/docs/index.html')->withStatus(302);
        });
    }
}
