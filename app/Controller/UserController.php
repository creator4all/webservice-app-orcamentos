<?php
namespace App\Controller;

use App\Model\UsuarioModel;
use App\Utils\InputSanitizer;
use App\Utils\CNPJValidator;
use App\Utils\ParceiroValidator;
use App\Utils\Validator;
use App\DAO\UsuarioDAO;
use App\DAO\ParceiroDAO;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;

class UserController extends Controller {
    public function editProfile(Request $request, Response $response, $args) {
        return $this->encapsular_response(function($request, $response, $args) {
            $authenticatedUser = $request->getAttribute('usuario');
            if (!$authenticatedUser) {
                return [
                    'statusCodeHttp' => 401,
                    'mensagem' => 'Usuário não autenticado.'
                ];
            }
            
            // Obtém e sanitiza dados do request
            $data = $request->getParsedBody();
            $data = is_array($data) ? $data : [];
            $data = InputSanitizer::sanitizeArray($data);
            
            $allowedFields = ['nome', 'email', 'telefone', 'foto_perfil', 'data_nascimento', 'cargo'];
            $updateData = [];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }
            
            if (empty($updateData)) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Nenhum campo válido para atualização foi fornecido.'
                ];
            }
            
            $errors = [];
            
            if (isset($updateData['email']) && !InputSanitizer::validateEmail($updateData['email'])) {
                $errors['email'] = 'E-mail em formato inválido.';
            }
            
            if (isset($updateData['data_nascimento'])) {
                $formattedDate = date('Y-m-d', strtotime($updateData['data_nascimento']));
                if ($formattedDate === false) {
                    $errors['data_nascimento'] = 'Data de nascimento em formato inválido.';
                } else {
                    $updateData['data_nascimento'] = $formattedDate;
                }
            }
            
            if (!empty($errors)) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Falha na validação dos campos.',
                    'erros' => $errors
                ];
            }
            
            $userDAO = new UsuarioDAO();
            $user = $userDAO->buscarPorId($authenticatedUser->sub);
            
            if (!$user) {
                return [
                    'statusCodeHttp' => 404,
                    'mensagem' => 'Usuário não encontrado.'
                ];
            }
            
            foreach ($updateData as $field => $value) {
                $user->$field = $value;
            }
            
            // Atualiza o usuário no banco
            $result = $userDAO->atualizar($user);
            
            if (!$result) {
                return [
                    'statusCodeHttp' => 500,
                    'mensagem' => 'Erro ao atualizar perfil do usuário.'
                ];
            }
            
            return [
                'statusCodeHttp' => 200,
                'status' => 'sucesso',
                'mensagem' => 'Perfil atualizado com sucesso.',
                'usuario' => [
                    'id' => $user->idUsuarios,
                    'nome' => $user->nome,
                    'email' => $user->email,
                    'telefone' => $user->telefone,
                    'foto_perfil' => $user->foto_perfil,
                    'data_nascimento' => $user->data_nascimento,
                    'cargo' => $user->cargo
                ]
            ];
        }, $request, $response, $args);
    }
    
    /**
     * Lista gestores e vendedores de acordo com as permissões do usuário autenticado
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function listManagers(Request $request, Response $response, $args) {
        return $this->encapsular_response(function($request, $response, $args) {
            // Verificar autenticação e permissão
            $user = $request->getAttribute('usuario');
            if (!$user || ($user->role_id != 1 && $user->role_id != 2)) { // Admin (1) ou Gestor (2)
                return [
                    'statusCodeHttp' => 403,
                    'mensagem' => 'Apenas administradores e gestores podem listar gestores e vendedores.'
                ];
            }
            
            $queryParams = $request->getQueryParams();
            $page = isset($queryParams['pagina']) ? (int)$queryParams['pagina'] : 1;
            $perPage = isset($queryParams['por_pagina']) ? (int)$queryParams['por_pagina'] : 10;
            
            // Validar parâmetros de paginação
            if ($page < 1) $page = 1;
            if ($perPage < 1 || $perPage > 100) $perPage = 10;
            
            $userDAO = new UsuarioDAO();
            $result = [];
            
            // Comportamento diferente baseado no papel do usuário
            if ($user->role_id == 1) { // Administrador - lista gestores e vendedores com informações da empresa
                $result = $userDAO->buscarGestoresEVendedores(null, true, $page, $perPage);
                
                $formattedUsers = [];
                foreach ($result['usuarios'] as $usr) {
                    $formattedUser = [
                        'id' => $usr->idUsuarios,
                        'nome' => $usr->nome,
                        'email' => $usr->email,
                        'telefone' => $usr->telefone,
                        'cargo' => $usr->cargo,
                        'status' => $usr->status,
                        'role_id' => $usr->role_id,
                        'empresa' => isset($usr->empresa) ? $usr->empresa : null
                    ];
                    $formattedUsers[] = $formattedUser;
                }
                
                return [
                    'statusCodeHttp' => 200,
                    'status' => 'sucesso',
                    'usuarios' => $formattedUsers,
                    'paginacao' => $result['paginacao']
                ];
                
            } else { // Gestor - lista apenas vendedores da sua empresa
                $result = $userDAO->buscarVendedores($user->parceiro_id, $page, $perPage);
                
                $formattedUsers = [];
                foreach ($result['usuarios'] as $usr) {
                    $formattedUser = [
                        'id' => $usr->idUsuarios,
                        'nome' => $usr->nome,
                        'email' => $usr->email,
                        'telefone' => $usr->telefone,
                        'cargo' => $usr->cargo,
                        'status' => $usr->status,
                        'role_id' => $usr->role_id
                    ];
                    $formattedUsers[] = $formattedUser;
                }
                
                return [
                    'statusCodeHttp' => 200,
                    'status' => 'sucesso',
                    'usuarios' => $formattedUsers,
                    'paginacao' => $result['paginacao']
                ];
            }
        }, $request, $response, $args);
    }
    
    public function register(Request $request, Response $response, $args) {
        return $this->encapsular_response(function($request, $response, $args) {
            // Verificar se o CNPJ foi fornecido
            if (!isset($args['cnpj'])) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'CNPJ do parceiro não fornecido.'
                ];
            }
            
            // Sanitizar e validar o CNPJ
            $cnpj = CNPJValidator::sanitizar($args['cnpj']);
            
            // Validar formato do CNPJ
            if (!CNPJValidator::validarFormato($cnpj)) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Formato de CNPJ inválido.'
                ];
            }
            
            // Validar parceiro pelo CNPJ
            $partnerDAO = new ParceiroDAO();
            $validationResult = ParceiroValidator::validarCNPJParceiro($cnpj, $partnerDAO);
            
            if (!$validationResult['valido']) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => $validationResult['mensagem']
                ];
            }
            
            // Obter parceiro validado
            $partner = $validationResult['parceiro'];
            
            // Obter e sanitizar dados do request
            $data = $request->getParsedBody();
            $data = is_array($data) ? $data : [];
            $data = InputSanitizer::sanitizeArray($data);
            
            // Validar dados do usuário
            $errors = InputSanitizer::validateUsuario($data);
            
            // Se houver erros de validação, retornar erro 400
            if (!empty($errors)) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Falha na validação dos campos.',
                    'erros' => $errors
                ];
            }
            
            // Criar modelo de usuário e hash da senha
            $user = new UsuarioModel($data);
            $user->password = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Associar o usuário ao parceiro
            $user->parceiros_idparceiros = $partner->idparceiros;
            
            // Inserir usuário no banco
            $userDAO = new UsuarioDAO();
            $result = $userDAO->inserir($user);
            
            if (!$result) {
                return [
                    'statusCodeHttp' => 500,
                    'mensagem' => 'Erro ao inserir usuário no banco de dados.'
                ];
            }
            
            // Retornar sucesso com status 201 (Created)
            return [
                'statusCodeHttp' => 201,
                'status' => 'sucesso',
                'mensagem' => 'Usuário cadastrado com sucesso!',
            ];
        }, $request, $response, $args);
    }
    
    public function signIn(Request $request, Response $response, $args) {
        return $this->encapsular_response(function($request, $response, $args) {
            // Obter e sanitizar dados do request
            $data = $request->getParsedBody();
            $data = is_array($data) ? $data : [];
            $data = InputSanitizer::sanitizeArray($data);

            $validation = Validator::validate($data, ['email', 'password']);
            
            if (!$validation['valido']) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Campos obrigatórios não fornecidos.',
                    'erros' => $validation['erros']
                ];
            }
            
            $userDAO = new UsuarioDAO();
            $user = $userDAO->buscarPorEmailESenha($data['email'], $data['password']);
            // Verificar se o usuário existe com as credenciais fornecidas
            if (!$user) {
                return [
                    'statusCodeHttp' => 401,
                    'mensagem' => 'Credenciais inválidas.'
                ];
            }
            
            $partnerDAO = new ParceiroDAO();
            $cnpj = null;
            if ($user->parceiros_idparceiros) {
                $partner = $partnerDAO->buscarPorId($user->parceiros_idparceiros);
                if ($partner) {
                    $cnpj = $partner->cnpj;
                }
            }
            
            $settings = $this->container->get('settings');
            $jwtSettings = $settings['jwt'];
            
            $issuedAt = time();
            $expirationTime = $issuedAt + $jwtSettings['expiration'];
            
            $payload = [
                'iat' => $issuedAt,
                'exp' => $expirationTime,
                'sub' => $user->idUsuarios,
                'email' => $user->email,
                'nome' => $user->nome,
                'role_id' => $user->role_id,
                'parceiro_id' => $user->parceiros_idparceiros
            ];
            
            $token = JWT::encode($payload, $jwtSettings['secret'], $jwtSettings['algorithm']);
            
            // Retornar token e dados do usuário
            return [
                'statusCodeHttp' => 200,
                'status' => 'sucesso',
                'mensagem' => 'Login realizado com sucesso!',
                'token' => $token,
                'usuario' => [
                    'id' => $user->idUsuarios,
                    'nome' => $user->nome,
                    'email' => $user->email,
                    'foto_perfil' => $user->foto_perfil,
                    'cnpj' => $cnpj,
                    'role_id' => $user->role_id
                ]
            ];
        }, $request, $response, $args);
    }
}
