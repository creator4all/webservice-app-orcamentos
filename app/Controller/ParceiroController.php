<?php
namespace App\Controller;

use App\Model\ParceiroModel;
use App\Model\UsuarioModel;
use App\Utils\InputSanitizer;
use App\Utils\CNPJValidator;
use App\Utils\ImageUtils;
use App\Utils\Validator;
use App\DAO\ParceiroDAO;
use App\DAO\UsuarioDAO;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ParceiroController extends Controller {
    /**
     * Edita um parceiro existente
     * Apenas administradores e gestores podem editar parceiros
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function editar(Request $request, Response $response, $args) {
        return $this->encapsular_response(function($request, $response, $args) {
            $usuario = $request->getAttribute('usuario');
            if (!$usuario || ($usuario->role_id != 1 && $usuario->role_id != 2)) { // Admin (1) ou Gestor (2)
                return [
                    'statusCodeHttp' => 403,
                    'mensagem' => 'Apenas administradores e gestores podem editar informações da empresa.'
                ];
            }
            
            if (!isset($args['id'])) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'ID do parceiro não fornecido.'
                ];
            }
            
            $parceiroId = $args['id'];
            
            if ($usuario->role_id == 2 && $usuario->parceiro_id != $parceiroId) {
                return [
                    'statusCodeHttp' => 403,
                    'mensagem' => 'Gestores só podem editar informações da própria empresa.'
                ];
            }
            
            $dados = $request->getParsedBody();
            $dados = is_array($dados) ? $dados : [];
            $dados = InputSanitizer::sanitizeArray($dados);
            
            $parceiroDAO = new ParceiroDAO();
            $parceiro = $parceiroDAO->buscarPorId($parceiroId);
            
            if (!$parceiro) {
                return [
                    'statusCodeHttp' => 404,
                    'mensagem' => 'Parceiro não encontrado.'
                ];
            }
            
            $erros = [];
            
            if (isset($dados['nome_fantasia']) && empty($dados['nome_fantasia'])) {
                $erros['nome_fantasia'] = 'Nome fantasia não pode ser vazio.';
            }
            
            if (isset($dados['razao_social']) && empty($dados['razao_social'])) {
                $erros['razao_social'] = 'Razão social não pode ser vazia.';
            }
            
            if (isset($dados['cnpj'])) {
                $cnpj = CNPJValidator::sanitizar($dados['cnpj']);
                if (!CNPJValidator::validarFormato($cnpj)) {
                    $erros['cnpj'] = 'Formato de CNPJ inválido.';
                } else {
                    $parceiroExistente = $parceiroDAO->buscarPorCNPJ($cnpj);
                    if ($parceiroExistente && $parceiroExistente->idparceiros != $parceiroId) {
                        $erros['cnpj'] = 'CNPJ já cadastrado para outro parceiro.';
                    }
                    $dados['cnpj'] = $cnpj;
                }
            }
            
            if (isset($dados['url']) && !InputSanitizer::validateUrl($dados['url'])) {
                $erros['url'] = 'URL em formato inválido.';
            }
            
            if (!empty($erros)) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Falha na validação dos campos.',
                    'erros' => $erros
                ];
            }
            
            if (isset($dados['nome_fantasia'])) $parceiro->nome_fantasia = $dados['nome_fantasia'];
            if (isset($dados['razao_social'])) $parceiro->razao_social = $dados['razao_social'];
            if (isset($dados['cnpj'])) $parceiro->cnpj = $dados['cnpj'];
            if (isset($dados['logomarca'])) $parceiro->logomarca = $dados['logomarca'];
            if (isset($dados['url'])) $parceiro->url = $dados['url'];
            
            $parceiro->updated_by = $usuario->sub;
            
            $resultado = $parceiroDAO->atualizar($parceiro);
            
            if (!$resultado) {
                return [
                    'statusCodeHttp' => 500,
                    'mensagem' => 'Erro ao atualizar informações do parceiro.'
                ];
            }
            
            return [
                'statusCodeHttp' => 200,
                'status' => 'sucesso',
                'mensagem' => 'Informações do parceiro atualizadas com sucesso.',
                'parceiro' => [
                    'id' => $parceiro->idparceiros,
                    'nome_fantasia' => $parceiro->nome_fantasia,
                    'razao_social' => $parceiro->razao_social,
                    'cnpj' => CNPJValidator::formatar($parceiro->cnpj),
                    'url' => $parceiro->url,
                    'logomarca' => $parceiro->logomarca
                ]
            ];
        }, $request, $response, $args);
    }
    
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
            
            $camposObrigatorios = ['cnpj', 'razao_social', 'nome_fantasia'];
            $validacao = Validator::validate($dados, $camposObrigatorios);
            
            if (!$validacao['valido']) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Campos obrigatórios não fornecidos.',
                    'erros' => $validacao['erros']
                ];
            }
            
            $cnpj = CNPJValidator::sanitizar($dados['cnpj']);
            if (!CNPJValidator::validarFormato($cnpj) || strlen($cnpj) != 14) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Formato de CNPJ inválido. O CNPJ deve conter exatamente 14 dígitos.'
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
            
            if (isset($dados['site_institucional']) && !empty($dados['site_institucional'])) {
                if (!InputSanitizer::validateUrl($dados['site_institucional'])) {
                    return [
                        'statusCodeHttp' => 400,
                        'mensagem' => 'URL do site institucional em formato inválido.'
                    ];
                }
            }
            
            $logomarcaPath = null;
            if (isset($dados['logomarca_url']) && !empty($dados['logomarca_url'])) {
                if (!ImageUtils::validateJpgImage($dados['logomarca_url'])) {
                    return [
                        'statusCodeHttp' => 400,
                        'mensagem' => 'Formato de imagem inválido. Apenas imagens JPG são aceitas.'
                    ];
                }
                
                $uploadDir = __DIR__ . '/../../public/uploads/logomarcas';
                $filename = 'logo_' . substr($cnpj, 0, 8) . '_' . uniqid();
                $logomarcaPath = ImageUtils::saveBase64Image(
                    $dados['logomarca_url'], 
                    $uploadDir, 
                    $filename
                );
                
                if (!$logomarcaPath) {
                    return [
                        'statusCodeHttp' => 500,
                        'mensagem' => 'Erro ao processar a imagem da logomarca.'
                    ];
                }
            }
            
            $this->pdo = \App\DAO\Connection::db();
            $this->pdo->beginTransaction();
            
            $parceiro = new ParceiroModel();
            $parceiro->cnpj = $cnpj;
            $parceiro->nome_fantasia = $dados['nome_fantasia'];
            $parceiro->razao_social = $dados['razao_social'];
            $parceiro->status = true; // Status sempre true conforme requisito
            $parceiro->gestor_cadastrado = false; // Flag indicando que não há gestor cadastrado
            
            if ($logomarcaPath) {
                $parceiro->logomarca = $logomarcaPath;
            }
            
            if (isset($dados['site_institucional']) && !empty($dados['site_institucional'])) {
                $parceiro->url = $dados['site_institucional'];
            }
            
            $parceiroId = $parceiroDAO->inserir($parceiro);
            
            if (!$parceiroId) {
                $this->pdo->rollBack();
                return [
                    'statusCodeHttp' => 500,
                    'mensagem' => 'Erro ao inserir parceiro no banco de dados.'
                ];
            }
            
            $this->pdo->commit();
            
            return [
                'statusCodeHttp' => 201,
                'status' => 'sucesso',
                'mensagem' => 'Parceiro cadastrado com sucesso!',
                'parceiro' => [
                    'id' => $parceiroId,
                    'cnpj' => CNPJValidator::formatar($cnpj),
                    'nome_fantasia' => $parceiro->nome_fantasia,
                    'razao_social' => $parceiro->razao_social,
                    'site_institucional' => $parceiro->url ?? null,
                    'logomarca' => $parceiro->logomarca ?? null
                ]
            ];
        }, $request, $response, $args);
    }
}
