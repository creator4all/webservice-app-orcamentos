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
        $sql = "INSERT INTO usuarios (nome, email, telefone, status, excluido, foto_perfil, created_at, updated_at, parceiros_idparceiros, cargo, password, role_id) 
        VALUES 
        (:nome, :email, :telefone, :status, :excluido, :foto_perfil, NOW(), NOW(), :parceiros_idparceiros, :cargo, :password, :role_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome', $usuario->nome);
        $stmt->bindValue(':email', $usuario->email);
        $stmt->bindValue(':telefone', $usuario->telefone);
        $stmt->bindValue(':status', $usuario->status);
        $stmt->bindValue(':excluido', $usuario->excluido);
        $stmt->bindValue(':foto_perfil', $usuario->foto_perfil);
        $stmt->bindValue(':parceiros_idparceiros', $usuario->parceiros_idparceiros);
        $stmt->bindValue(':cargo', $usuario->cargo ?? 'Gestor');
        $stmt->bindValue(':password', $usuario->password);
        $stmt->bindValue(':role_id', $usuario->role_id);
        return $stmt->execute();
    }
    
    public function buscarPorEmailESenha($email, $password) {
        $sql = "SELECT * FROM usuarios WHERE email = :email AND password = :password AND excluido = 0 AND status = 1 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':password', $password);
        $stmt->execute();
        
        $dados = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$dados) {
            return null;
        }
        
        $usuario = new UsuarioModel($dados);
        return $usuario;
    }
    
    /**
     * Busca um usuário pelo email
     * @param string $email Email do usuário
     * @return UsuarioModel|null Usuário encontrado ou null
     */
    public function buscarPorEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = :email AND excluido = 0 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        
        $dados = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$dados) {
            return null;
        }
        
        $usuario = new UsuarioModel($dados);
        return $usuario;
    }
}
