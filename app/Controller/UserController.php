<?php
namespace App\Controller;

use App\Service\UsuarioService\UsuarioService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class UserController extends Controller {
    // TODO: Listar todos os usuarios
    public function list_users(Request $request, Response $response, array $args): array {
        return $this->encapsular_response(function (Request $request, Response $response, array $args){
            $data = $request->getAttribute('usuario');
            $usuarioService = new UsuarioService();
            return $usuarioService->list_users($data);
        }, $request, $response, $args);
    }
    // TODO: Listar usuarios por função
    public function list_users_by_role(Request $request, Response $response, array $args): array {
        return $this->encapsular_response(function (Request $request, Response $response, array $args){
            $data = $request->getParsedBody();
            $usuario = $request->getAttribute('usuario');
            $usuarioService = new UsuarioService();
            return $usuarioService->list_users_by_role($data['role_id'], $usuario);
        }, $request, $response, $args);
    }
    // TODO: Listar usuarios por parceiro
    public function list_users_by_partner(Request $request, Response $response, array $args): array {
        return $this->encapsular_response(function (Request $request, Response $response, array $args){
            $data = $request->getParsedBody();
            $usuario = $request->getAttribute('usuario');
            $usuarioService = new UsuarioService();
            return $usuarioService->list_users_by_partner($data['parceiro_id'], $usuario);
        }, $request, $response, $args);
    }
    // TODO: Cadastrar usuario
    public function create_user(Request $request, Response $response, array $args): array {
        return $this->encapsular_response(function (Request $request, Response $response, array $args){
            $data = $request->getParsedBody();
            $usuarioService = new UsuarioService();
            return $usuarioService->signup($data);
        }, $request, $response, $args);
    }
    // TODO: Atualizar usuario
    public function update_user(Request $request, Response $response, array $args): array {
        return $this->encapsular_response(function (Request $request, Response $response, array $args){
            $data = $request->getParsedBody();
            $usuario = $request->getAttribute('usuario');
            $usuarioService = new UsuarioService();
            return $usuarioService->update_user($data, $usuario);
        }, $request, $response, $args);
    }
    // TODO: Deletar usuario
    public function delete_user(Request $request, Response $response, array $args): array {
        return $this->encapsular_response(function (Request $request, Response $response, array $args){
            $data = $request->getParsedBody();
            $usuario = $request->getAttribute('usuario');
            $usuarioService = new UsuarioService();
            return $usuarioService->delete_user($data['id'], $usuario);
        }, $request, $response, $args);
    }
    // TODO: Buscar usuario por email e senha e retornar login
    public function login(Request $request, Response $response, array $args): array {
        return $this->encapsular_response(function (Request $request, Response $response, array $args){
            $data = $request->getParsedBody();
            $usuarioService = new UsuarioService();
            return $usuarioService->signin($data);
        }, $request, $response, $args);
    }
    // TODO: Resetar senha do usuario

}
