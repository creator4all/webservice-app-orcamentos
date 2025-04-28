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

class UsuarioController extends Controller {
    public function editarPerfil(Request $request, Response $response, $args) {
        return $this->encapsular_response(function($request, $response, $args) {
            $usuarioAutenticado = $request->getAttribute('usuario');
            if (!$usuarioAutenticado) {
                return [
                    'statusCodeHttp' => 401,
                    'mensagem' => 'Usuário não autenticado.'
                ];
            }
            
            // Obtém e sanitiza dados do request
            $dados = $request->getParsedBody();
            $dados = is_array($dados) ? $dados : [];
            $dados = InputSanitizer::sanitizeArray($dados);
            
            $camposPermitidos = ['nome', 'email', 'telefone', 'foto_perfil', 'data_nascimento', 'cargo'];
            $dadosAtualizacao = [];
            
            foreach ($camposPermitidos as $campo) {
                if (isset($dados[$campo])) {
                    $dadosAtualizacao[$campo] = $dados[$campo];
                }
            }
            
            if (empty($dadosAtualizacao)) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Nenhum campo válido para atualização foi fornecido.'
                ];
            }
            
            $erros = [];
            
            if (isset($dadosAtualizacao['email']) && !InputSanitizer::validateEmail($dadosAtualizacao['email'])) {
                $erros['email'] = 'E-mail em formato inválido.';
            }
            
            if (isset($dadosAtualizacao['data_nascimento'])) {
                $dataFormatada = date('Y-m-d', strtotime($dadosAtualizacao['data_nascimento']));
                if ($dataFormatada === false) {
                    $erros['data_nascimento'] = 'Data de nascimento em formato inválido.';
                } else {
                    $dadosAtualizacao['data_nascimento'] = $dataFormatada;
                }
            }
            
            if (!empty($erros)) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Falha na validação dos campos.',
                    'erros' => $erros
                ];
            }
            
            $usuarioDAO = new UsuarioDAO();
            $usuario = $usuarioDAO->buscarPorId($usuarioAutenticado->sub);
            
            if (!$usuario) {
                return [
                    'statusCodeHttp' => 404,
                    'mensagem' => 'Usuário não encontrado.'
                ];
            }
            
            foreach ($dadosAtualizacao as $campo => $valor) {
                $usuario->$campo = $valor;
            }
            
            // Atualiza o usuário no banco
            $resultado = $usuarioDAO->atualizar($usuario);
            
            if (!$resultado) {
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
                    'id' => $usuario->idUsuarios,
                    'nome' => $usuario->nome,
                    'email' => $usuario->email,
                    'telefone' => $usuario->telefone,
                    'foto_perfil' => $usuario->foto_perfil,
                    'data_nascimento' => $usuario->data_nascimento,
                    'cargo' => $usuario->cargo
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
    public function listarGestores(Request $request, Response $response, $args) {
        return $this->encapsular_response(function($request, $response, $args) {
            // Verificar autenticação e permissão
            $usuario = $request->getAttribute('usuario');
            if (!$usuario || ($usuario->role_id != 1 && $usuario->role_id != 2)) { // Admin (1) ou Gestor (2)
                return [
                    'statusCodeHttp' => 403,
                    'mensagem' => 'Apenas administradores e gestores podem listar gestores e vendedores.'
                ];
            }
            
            $queryParams = $request->getQueryParams();
            $pagina = isset($queryParams['pagina']) ? (int)$queryParams['pagina'] : 1;
            $porPagina = isset($queryParams['por_pagina']) ? (int)$queryParams['por_pagina'] : 10;
            
            // Validar parâmetros de paginação
            if ($pagina < 1) $pagina = 1;
            if ($porPagina < 1 || $porPagina > 100) $porPagina = 10;
            
            $usuarioDAO = new UsuarioDAO();
            $resultado = [];
            
            // Comportamento diferente baseado no papel do usuário
            if ($usuario->role_id == 1) { // Administrador - lista gestores e vendedores com informações da empresa
                $resultado = $usuarioDAO->buscarGestoresEVendedores(null, true, $pagina, $porPagina);
                
                $usuariosFormatados = [];
                foreach ($resultado['usuarios'] as $usr) {
                    $usuarioFormatado = [
                        'id' => $usr->idUsuarios,
                        'nome' => $usr->nome,
                        'email' => $usr->email,
                        'telefone' => $usr->telefone,
                        'cargo' => $usr->cargo,
                        'status' => $usr->status,
                        'role_id' => $usr->role_id,
                        'empresa' => isset($usr->empresa) ? $usr->empresa : null
                    ];
                    $usuariosFormatados[] = $usuarioFormatado;
                }
                
                return [
                    'statusCodeHttp' => 200,
                    'status' => 'sucesso',
                    'usuarios' => $usuariosFormatados,
                    'paginacao' => $resultado['paginacao']
                ];
                
            } else { // Gestor - lista apenas vendedores da sua empresa
                $resultado = $usuarioDAO->buscarVendedores($usuario->parceiro_id, $pagina, $porPagina);
                
                $usuariosFormatados = [];
                foreach ($resultado['usuarios'] as $usr) {
                    $usuarioFormatado = [
                        'id' => $usr->idUsuarios,
                        'nome' => $usr->nome,
                        'email' => $usr->email,
                        'telefone' => $usr->telefone,
                        'cargo' => $usr->cargo,
                        'status' => $usr->status,
                        'role_id' => $usr->role_id
                    ];
                    $usuariosFormatados[] = $usuarioFormatado;
                }
                
                return [
                    'statusCodeHttp' => 200,
                    'status' => 'sucesso',
                    'usuarios' => $usuariosFormatados,
                    'paginacao' => $resultado['paginacao']
                ];
            }
        }, $request, $response, $args);
    }
    
    public function cadastrar(Request $request, Response $response, $args) {
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
            $parceiroDAO = new ParceiroDAO();
            $resultadoValidacao = ParceiroValidator::validarCNPJParceiro($cnpj, $parceiroDAO);
            
            if (!$resultadoValidacao['valido']) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => $resultadoValidacao['mensagem']
                ];
            }
            
            // Obter parceiro validado
            $parceiro = $resultadoValidacao['parceiro'];
            
            // Obter e sanitizar dados do request
            $dados = $request->getParsedBody();
            $dados = is_array($dados) ? $dados : [];
            $dados = InputSanitizer::sanitizeArray($dados);
            
            // Validar dados do usuário
            $erros = InputSanitizer::validateUsuario($dados);
            
            // Se houver erros de validação, retornar erro 400
            if (!empty($erros)) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Falha na validação dos campos.',
                    'erros' => $erros
                ];
            }
            
            // Criar modelo de usuário e hash da senha
            $usuario = new UsuarioModel($dados);
            $usuario->password = password_hash($dados['password'], PASSWORD_DEFAULT);
            
            // Associar o usuário ao parceiro
            $usuario->parceiros_idparceiros = $parceiro->idparceiros;
            
            // Inserir usuário no banco
            $usuarioDAO = new UsuarioDAO();
            $resultado = $usuarioDAO->inserir($usuario);
            
            if (!$resultado) {
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
    
    public function signin(Request $request, Response $response, $args) {
        return $this->encapsular_response(function($request, $response, $args) {
            // Obter e sanitizar dados do request
            $dados = $request->getParsedBody();
            $dados = is_array($dados) ? $dados : [];
            $dados = InputSanitizer::sanitizeArray($dados);

            $validacao = Validator::validate($dados, ['email', 'password']);
            
            if (!$validacao['valido']) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Campos obrigatórios não fornecidos.',
                    'erros' => $validacao['erros']
                ];
            }
            
            $usuarioDAO = new UsuarioDAO();
            $usuario = $usuarioDAO->buscarPorEmailESenha($dados['email'], $dados['password']);
            // Verificar se o usuário existe com as credenciais fornecidas
            if (!$usuario) {
                return [
                    'statusCodeHttp' => 401,
                    'mensagem' => 'Credenciais inválidas.'
                ];
            }
            
            $settings = $this->container->get('settings');
            $jwtSettings = $settings['jwt'];
            
            $issuedAt = time();
            $expirationTime = $issuedAt + $jwtSettings['expiration'];
            
            $payload = [
                'iat' => $issuedAt,
                'exp' => $expirationTime,
                'sub' => $usuario->idUsuarios,
                'email' => $usuario->email,
                'nome' => $usuario->nome,
                'role_id' => $usuario->role_id,
                'parceiro_id' => $usuario->parceiros_idparceiros
            ];
            
            $token = JWT::encode($payload, $jwtSettings['secret'], $jwtSettings['algorithm']);
            
            // Retornar token e dados do usuário
            return [
                'statusCodeHttp' => 200,
                'status' => 'sucesso',
                'mensagem' => 'Login realizado com sucesso!',
                'token' => $token,
                'usuario' => [
                    'id' => $usuario->idUsuarios,
                    'nome' => $usuario->nome,
                    'email' => $usuario->email,
                    'role_id' => $usuario->role_id
                ]
            ];
        }, $request, $response, $args);
    }
}
