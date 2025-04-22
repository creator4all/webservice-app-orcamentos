<?php
namespace App\Controller;

use App\Model\UsuarioModel;
use App\DAO\UsuarioDAO;
use App\Utils\InputSanitizer;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;

class AuthController extends \App\Controller\Controller {
    /**
     * Login de usuário
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function login(Request $request, Response $response, $args) {
        return $this->encapsular_response(function($request, $response, $args) {
            $dados = $request->getParsedBody();
            $dados = is_array($dados) ? $dados : [];
            $dados = InputSanitizer::sanitizeArray($dados);
            
            if (!isset($dados['email']) || !isset($dados['password'])) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Email e senha são obrigatórios'
                ];
            }
            
            $usuarioDAO = new UsuarioDAO();
            $usuario = $usuarioDAO->buscarPorEmail($dados['email']);
            
            if (!$usuario) {
                return [
                    'statusCodeHttp' => 401,
                    'mensagem' => 'Credenciais inválidas'
                ];
            }
            
            if (!password_verify($dados['password'], $usuario->password)) {
                return [
                    'statusCodeHttp' => 401,
                    'mensagem' => 'Credenciais inválidas'
                ];
            }
            
            $jwt = $this->gerarToken($usuario);
            
            return [
                'statusCodeHttp' => 200,
                'status' => 'sucesso',
                'token' => $jwt,
                'usuario' => [
                    'id' => $usuario->idUsuarios,
                    'nome' => $usuario->nome,
                    'email' => $usuario->email,
                    'role_id' => $usuario->role_id
                ]
            ];
        }, $request, $response, $args);
    }
    
    /**
     * Gera um token JWT para o usuário
     * @param UsuarioModel $usuario
     * @return string
     */
    private function gerarToken(UsuarioModel $usuario) {
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600; // Token válido por 1 hora
        
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'sub' => $usuario->idUsuarios,
            'email' => $usuario->email,
            'nome' => $usuario->nome,
            'role_id' => $usuario->role_id
        ];
        
        $jwt = JWT::encode(
            $payload,
            $_ENV['JWT_SECRET'] ?? 'your_jwt_secret_key',
            $_ENV['JWT_ALGORITHM'] ?? 'HS256'
        );
        
        return $jwt;
    }
    
    /**
     * Registro de usuário
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function register(Request $request, Response $response, $args) {
        return $this->encapsular_response(function($request, $response, $args) {
            $dados = $request->getParsedBody();
            $dados = is_array($dados) ? $dados : [];
            $dados = InputSanitizer::sanitizeArray($dados);
            
            $camposObrigatorios = ['nome', 'email', 'password', 'telefone'];
            foreach ($camposObrigatorios as $campo) {
                if (!isset($dados[$campo]) || empty($dados[$campo])) {
                    return [
                        'statusCodeHttp' => 400,
                        'mensagem' => "Campo obrigatório não fornecido: {$campo}"
                    ];
                }
            }
            
            $usuarioDAO = new UsuarioDAO();
            $usuarioExistente = $usuarioDAO->buscarPorEmail($dados['email']);
            
            if ($usuarioExistente) {
                return [
                    'statusCodeHttp' => 409,
                    'mensagem' => 'Email já cadastrado'
                ];
            }
            
            $usuario = new UsuarioModel();
            $usuario->nome = $dados['nome'];
            $usuario->email = $dados['email'];
            $usuario->telefone = $dados['telefone'];
            $usuario->password = password_hash($dados['password'], PASSWORD_DEFAULT);
            $usuario->status = true;
            $usuario->excluido = false;
            $usuario->role_id = 2; // Usuário comum
            
            $resultado = $usuarioDAO->inserir($usuario);
            
            if (!$resultado) {
                return [
                    'statusCodeHttp' => 500,
                    'mensagem' => 'Erro ao cadastrar usuário'
                ];
            }
            
            return [
                'statusCodeHttp' => 201,
                'status' => 'sucesso',
                'mensagem' => 'Usuário cadastrado com sucesso',
                'usuario' => [
                    'id' => $resultado,
                    'nome' => $usuario->nome,
                    'email' => $usuario->email
                ]
            ];
        }, $request, $response, $args);
    }
}
