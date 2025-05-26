<?php

namespace App\Service;

use App\Config\DatabaseConnection;
use App\DAO\ParceiroDAO;
use App\DAO\UsuarioDAO;
use App\Model\ParceiroModel;
use App\Model\UsuarioModel;
use App\Utils\Utils;

class UsuarioService {
    protected $db;

    public function __construct() {
        $this->db = DatabaseConnection::getInstance();
    }

    public function signin($usuario){
        $valid_form = ['email', 'password'];

        if(!Utils::valid_form($usuario, $valid_form)) {
            return \HttpResponse::http_response(400, 'Dados incompletos.');
        }

        $usuarioModel = new UsuarioModel($usuario);
        $usuarioDAO = new UsuarioDAO($this->db);

        try{
            $usuarioDAO->buscarPorEmailESenha($usuarioModel);
            return \HttpResponse::http_response(200, 'Login realizado com sucesso.', array($usuarioModel));
        }catch(\Exception $e){
            return \HttpResponse::http_response(500, "Erro ao realizar login: " . $e->getMessage());
        }
    }

    public function signup($usuario){
        $valid_form = ['nome', 'email', 'telefone', 'password', 'parceiros_idparceiros', 'cargos_idcargos'];
        if(!Utils::valid_form($usuario, $valid_form)) {
            return \HttpResponse::http_response(400, 'Dados incompletos.');
        }

        $parceiroModel = new ParceiroModel([
            'idparceiros' => $usuario['parceiros_idparceiros']
        ]);
        $parceiroDAO = new ParceiroDAO($this->db);

        if(!$parceiroDAO->buscarPorId($parceiroModel)){
            return \HttpResponse::http_response(404, 'Parceiro não encontrado.');
        }

        $usuarioModel = new UsuarioModel($usuario);
        $usuarioDAO = new UsuarioDAO($this->db);

        try{
            $this->db->beginTransaction();
            $usuarioDAO->inserir($usuarioModel);
            $this->db->commit();
            return \HttpResponse::http_response(201, 'Usuário cadastrado com sucesso.', array($usuarioModel));
        }catch(\Exception $e){
            $this->db->rollBack();
            return \HttpResponse::http_response(500, "Erro ao cadastrar usuário: " . $e->getMessage());
        }
    }

    public function list_users_by_role($role_id, $usuario){
        if(!Utils::user_role($usuario["role_id"], [1])) {
            return \HttpResponse::http_response(403, "Acesso negado. Apenas administradores ou gestores podem listar usuários por função.");
        }

        $usuarioModel = new UsuarioModel($usuario);
        $usuarioDAO = new UsuarioDAO($this->db);

        try{
            $usuarios = $usuarioDAO->buscarPorFuncao($role_id, $usuarioModel->idUsuarios);
            return \HttpResponse::http_response(200, 'Usuários encontrados com sucesso.', array($usuarios));
        }catch(\Exception $e){
            return \HttpResponse::http_response(500, 'Erro ao buscar usuários por função: ' . $e->getMessage());
        }
    }

    public function list_users($usuario, $id_parceiro = null){
        if(!Utils::user_role($usuario["role_id"], [1,2])) {
            return \HttpResponse::http_response(403, "Acesso negado. Apenas administradores ou gestores podem listar usuários por função.");
        }

        if(!isset($id_parceiro) || empty($id_parceiro)) {
            return \HttpResponse::http_response(400, 'ID do parceiro não informado.');
        }

        $usuarioModel = new UsuarioModel($usuario);
        $usuarioDAO = new UsuarioDAO($this->db);

        try{
            $usuarios = $usuarioDAO->buscarTodosUsuarios($id_parceiro ?? $usuarioModel->parceiros_idparceiros);
            return \HttpResponse::http_response(200, 'Usuários encontrados com sucesso.', array($usuarios));
        }catch(\Exception $e){
            return \HttpResponse::http_response(500, 'Erro ao listar usuários: ' . $e->getMessage());
        }
    }

    public function list_users_by_partner($usuario, $id_parceiro){
        if(!Utils::user_role($usuario["role_id"], [1])) {
            return \HttpResponse::http_response(403, "Acesso negado. Apenas administradores ou gestores podem listar usuários por função.");
        }

        $usuarioDAO = new UsuarioDAO($this->db);

        try{
            return \HttpResponse::http_response(200, 'Usuários encontrados com sucesso.', array($usuarioDAO->buscarTodosUsuarios($id_parceiro)));
        }catch(\Exception $e){
            return \HttpResponse::http_response(500, 'Erro ao buscar usuários por parceiro: ' . $e->getMessage());
        }
    }

    public function update_user($usuario, $usuario_logado){
        $valid_form = ['idUsuarios', 'nome', 'email', 'telefone', 'status', 'excluido', 'foto_perfil', 'data_nascimento', 'parceiros_idparceiros', 'cargos_idcargos'];
        if(!Utils::valid_form($usuario, $valid_form)) {
            return \HttpResponse::http_response(400, 'Dados incompletos.');
        }

        $usuarioModel = new UsuarioModel($usuario);
        $usuarioDAO = new UsuarioDAO($this->db);

        try{
            $this->db->beginTransaction();
            $usuarioDAO->atualizar($usuarioModel, $usuario_logado);
            $this->db->commit();
            return \HttpResponse::http_response(200, 'Usuário atualizado com sucesso.', array($usuarioModel));
        }catch(\Exception $e){
            $this->db->rollBack();
            return \HttpResponse::http_response(500, 'Erro ao atualizar usuário: ' . $e->getMessage());
        }
    }

    public function delete_user($id, $usuario_logado){
        if(!Utils::user_role($usuario_logado["role_id"], [1,2]) || ($usuario_logado["id"] != $id && Utils::user_role($usuario_logado["role_id"], [3]))) {
            return \HttpResponse::http_response(403, "Acesso negado. Você deve estar autenticado.");
        }


        if(!isset($id) || empty($id)) {
            return \HttpResponse::http_response(400, 'ID do usuário não informado.');
        }

        $usuarioModel = new UsuarioModel(['idUsuarios' => $id]);
        $usuarioDAO = new UsuarioDAO($this->db);

        try{
            $this->db->beginTransaction();
            $usuarioDAO->deletar($usuarioModel, $usuario_logado);
            $this->db->commit();
            return \HttpResponse::http_response(200, 'Usuário deletado com sucesso.');
        }catch(\Exception $e){
            $this->db->rollBack();
            return \HttpResponse::http_response(500, 'Erro ao deletar usuário: ' . $e->getMessage());
        }
    }
}