<?php
namespace App\Controller;

use App\Model\ParceiroModel;
use App\Model\UsuarioModel;
use App\Utils\InputSanitizer;
use App\Utils\CNPJValidator;
use App\DAO\ParceiroDAO;
use App\DAO\UsuarioDAO;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ParceiroController extends Controller {
    /**
     * Cadastra um novo parceiro e seu usuário gestor
     * Apenas administradores podem cadastrar parceiros
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function cadastrar(Request $request, Response $response, $args) {
        return $this->encapsular_response(function($request, $response, $args) {
            $usuario = $request->getAttribute('usuario');
            if (!$usuario || $usuario->role_id != 1) { // Assumindo que role_id 1 é administrador
                return [
                    'statusCodeHttp' => 403,
                    'mensagem' => 'Apenas administradores podem cadastrar parceiros.'
                ];
            }
            
            $dados = $request->getParsedBody();
            $dados = is_array($dados) ? $dados : [];
            $dados = InputSanitizer::sanitizeArray($dados);
            
            $camposObrigatorios = ['cnpj', 'nome_fantasia', 'razao_social', 'usuario'];
            if (!$this->validar($camposObrigatorios)) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Campos obrigatórios não fornecidos.'
                ];
            }
            
            $cnpj = CNPJValidator::sanitizar($dados['cnpj']);
            if (!CNPJValidator::validarFormato($cnpj)) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Formato de CNPJ inválido.'
                ];
            }
            
            $parceiroDAO = new ParceiroDAO();
            $parceiroExistente = $parceiroDAO->buscarPorCNPJ($cnpj);
            if ($parceiroExistente) {
                return [
                    'statusCodeHttp' => 409,
                    'mensagem' => 'Já existe um parceiro cadastrado com este CNPJ.'
                ];
            }
            
            if (!isset($dados['usuario']) || !is_array($dados['usuario'])) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Dados do usuário gestor não fornecidos.'
                ];
            }
            
            $dadosUsuario = $dados['usuario'];
            $camposObrigatoriosUsuario = ['nome', 'email', 'telefone', 'password'];
            foreach ($camposObrigatoriosUsuario as $campo) {
                if (!isset($dadosUsuario[$campo]) || empty($dadosUsuario[$campo])) {
                    return [
                        'statusCodeHttp' => 400,
                        'mensagem' => "Campo obrigatório do usuário não fornecido: {$campo}"
                    ];
                }
            }
            
            $this->pdo = \App\DAO\Connection::db();
            $this->pdo->beginTransaction();
            
            try {
                $parceiro = new ParceiroModel();
                $parceiro->cnpj = $cnpj;
                $parceiro->nome_fantasia = $dados['nome_fantasia'];
                $parceiro->razao_social = $dados['razao_social'];
                $parceiro->status = true; // Status sempre true conforme requisito
                
                if (isset($dados['logomarca']) && !empty($dados['logomarca'])) {
                    $parceiro->logomarca = $dados['logomarca'];
                }
                
                $parceiroId = $parceiroDAO->inserir($parceiro);
                
                if (!$parceiroId) {
                    throw new \Exception('Erro ao inserir parceiro no banco de dados.');
                }
                
                $usuario = new UsuarioModel();
                $usuario->nome = $dadosUsuario['nome'];
                $usuario->email = $dadosUsuario['email'];
                $usuario->telefone = $dadosUsuario['telefone'];
                $usuario->password = password_hash($dadosUsuario['password'], PASSWORD_DEFAULT);
                $usuario->status = 1;
                $usuario->excluido = 0;
                $usuario->parceiros_idparceiros = $parceiroId;
                $usuario->cargo = 'Gestor';
                $usuario->role_id = 2; // Assumindo que role 2 é gestor de parceiro
                
                $usuarioDAO = new UsuarioDAO();
                $resultado = $usuarioDAO->inserir($usuario);
                
                if (!$resultado) {
                    throw new \Exception('Erro ao inserir usuário gestor no banco de dados.');
                }
                
                $this->pdo->commit();
                
                return [
                    'statusCodeHttp' => 201,
                    'status' => 'sucesso',
                    'mensagem' => 'Parceiro e usuário gestor cadastrados com sucesso!',
                    'parceiro' => [
                        'id' => $parceiroId,
                        'cnpj' => $cnpj,
                        'nome_fantasia' => $parceiro->nome_fantasia,
                        'razao_social' => $parceiro->razao_social
                    ]
                ];
                
            } catch (\Exception $e) {
                $this->pdo->rollBack();
                
                return [
                    'statusCodeHttp' => 500,
                    'mensagem' => 'Erro ao cadastrar parceiro: ' . $e->getMessage()
                ];
            }
        }, $request, $response, $args);
    }
}
