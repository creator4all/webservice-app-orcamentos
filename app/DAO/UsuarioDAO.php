<?php
namespace App\DAO;

use App\Model\UsuarioModel;
use App\DAO\Connection;


class UsuarioDAO extends Connection{
    private $pdo;

    public function __construct(){
        $this->pdo = Connection::db();
    }

    public function inserir(UsuarioModel $usuario) {
        $sql = "INSERT INTO usuarios (nome, email, telefone, status, excluido, foto_perfil, created_at, updated_at, parceiros_idparceiros, cargos_idcargos, password, role_id) 
        VALUES 
        (:nome, :email, :telefone, :status, :excluido, :foto_perfil, NOW(), NOW(), :parceiros_idparceiros, :cargos_idcargos, :password, :role_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome', $usuario->nome);
        $stmt->bindValue(':email', $usuario->email);
        $stmt->bindValue(':telefone', $usuario->telefone);
        $stmt->bindValue(':status', $usuario->status);
        $stmt->bindValue(':excluido', $usuario->excluido);
        $stmt->bindValue(':foto_perfil', $usuario->foto_perfil);
        $stmt->bindValue(':parceiros_idparceiros', $usuario->parceiros_idparceiros);
        $stmt->bindValue(':cargos_idcargos', $usuario->cargos_idcargos);
        $stmt->bindValue(':password', $usuario->password);
        $stmt->bindValue(':role_id', $usuario->role_id);
        return $stmt->execute();
    }
    
    public function buscarPorEmailESenha($email, $password) {
        $sql = "SELECT * FROM usuarios WHERE email = :email AND excluido = 0 AND status = 1 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        
        $dados = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$dados) {
            return null;
        }else if(!password_verify($password, $dados['password'])) {
            return null;
        }
        
        $usuario = new UsuarioModel($dados);
        return $usuario;
    }
}
