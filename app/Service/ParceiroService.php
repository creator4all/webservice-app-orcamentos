<?php

namespace App\Service;
use App\Config\DatabaseConnection;
use App\DAO\ParceiroDAO;
use App\Model\ParceiroModel;
use App\Utils\ParceiroUtils\ParceiroUtils;
use App\Utils\Utils;
use App\Model\UsuarioModel;

class ParceiroService {
    protected DatabaseConnection $db;

    public function __construct() {
        // TODO: Inserir na injeção de dependências
        $this->db = DatabaseConnection::getInstance();
    }

    public function edit(array $usuario, array $parceiro, $args = []): array
    {
        //$usuario = new UsuarioModel($usuario);
        if(!ParceiroUtils::valid_permission_edit($usuario)) {
            return \HttpResponse::http_response(403, 'Você não tem permissão para editar este parceiro.');
        }

        $valid_form = ['idparceiros', 'cnpj', 'logomarca', 'nome_fantasia', 'razao_social', 'status'];

        if(!Utils::valid_form($parceiro, $valid_form)) {
            return \HttpResponse::http_response(400, 'Dados incompletos.');
        }

        $parceiroModel = new ParceiroModel($parceiro);

        $parceiroDAO = new ParceiroDAO($this->db);
        $parceiroDAO->buscarPorCNPJ($parceiroModel);

        if(!$parceiro){
            return \HttpResponse::http_response(404, 'Parceiro não encontrado.');
        }

        if(!empty($parceiro['logomarca'])){
            $logomarca_path = ParceiroUtils::insert_logomarca($parceiro['logomarca'], substr($parceiroModel->cnpj, 0, 8));
            if(!$logomarca_path) {
                return \HttpResponse::http_response(500, 'Erro ao salvar logomarca.');
            }
            $parceiroModel->logomarca = $logomarca_path;
        } else {
            $parceiroModel->logomarca = null;
        }       

        //TODO: sanitizar os dados

        try{
            $this->db->beginTransaction();
            $parceiroDAO->atualizar($parceiroModel);
            $this->db->commit();
            //TODO: Log de atualização
            return \HttpResponse::http_response(200, 'Parceiro atualizado com sucesso.');
        }catch(\Exception $e){
            $this->db->rollBack();
            return \HttpResponse::http_response(500, "Erro ao atualizar parceiro: " . $e->getMessage());
        }
    }

    public function create(array $parceiro): array
    {
        $valid_form = ['cnpj', 'logomarca', 'nome_fantasia', 'razao_social', 'status', 'url'];

        if(!Utils::valid_form($parceiro, $valid_form)) {
            return \HttpResponse::http_response(400, 'Dados incompletos.');
        }

        $parceiroModel = new ParceiroModel($parceiro);
        $parceiroDAO = new ParceiroDAO($this->db);

        if($parceiroDAO->buscarPorCNPJ($parceiroModel->cnpj)){
            return \HttpResponse::http_response(409, 'Parceiro já cadastrado.');
        }

        if(!empty($parceiro['logomarca'])){
            $logomarca_path = ParceiroUtils::insert_logomarca($parceiro['logomarca'], substr($parceiroModel->cnpj, 0, 8));
            if(!$logomarca_path) {
                return \HttpResponse::http_response(500, 'Erro ao salvar logomarca.');
            }
            $parceiroModel->logomarca = $logomarca_path;
        } else {
            $parceiroModel->logomarca = null;
        }

        try{
            $this->db->beginTransaction();
            $id = $parceiroDAO->inserir($parceiroModel);
            $this->db->commit();
            return \HttpResponse::http_response(201, 'Parceiro cadastrado com sucesso.');
        } catch(\Exception $e) {
            $this->db->rollBack();
            return \HttpResponse::http_response(500, "Erro ao cadastrar parceiro: " . $e->getMessage());
        }
    }


}