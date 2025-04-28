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
    
    public function atualizar(UsuarioModel $usuario) {
        $sql = "UPDATE usuarios SET 
                nome = :nome, 
                email = :email, 
                telefone = :telefone, 
                foto_perfil = :foto_perfil, 
                data_nascimento = :data_nascimento, 
                cargo = :cargo,
                updated_at = NOW()
                WHERE idUsuarios = :id AND excluido = 0";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome', $usuario->nome);
        $stmt->bindValue(':email', $usuario->email);
        $stmt->bindValue(':telefone', $usuario->telefone);
        $stmt->bindValue(':foto_perfil', $usuario->foto_perfil);
        $stmt->bindValue(':data_nascimento', $usuario->data_nascimento);
        $stmt->bindValue(':cargo', $usuario->cargo);
        $stmt->bindValue(':id', $usuario->idUsuarios);
        
        return $stmt->execute();
    }
    
    public function buscarPorId($id) {
        $sql = "SELECT * FROM usuarios WHERE idUsuarios = :id AND excluido = 0 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        
        $dados = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$dados) {
            return null;
        }
        
        $usuario = new UsuarioModel($dados);
        return $usuario;
    }
}
