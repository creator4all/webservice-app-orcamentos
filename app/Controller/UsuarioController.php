<?php
namespace App\Controller;

use App\Model\UsuarioModel;
use App\Utils\InputSanitizer;
use App\Utils\CNPJValidator;
use App\Utils\ParceiroValidator;
use App\DAO\UsuarioDAO;
use App\DAO\ParceiroDAO;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class UsuarioController extends Controller {
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
}
