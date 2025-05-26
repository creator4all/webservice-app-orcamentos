<?php
namespace App\DAO;

use App\Model\UsuarioModel;
use App\DAO\Connection;


class UsuarioDAO{
    private $pdo;

    public function __construct(\PDO $pdo){
        $this->pdo = $pdo;
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
    
    public function buscarPorEmailESenha(UsuarioModel $usuario) {
        $sql = "SELECT * FROM usuarios WHERE email = :email AND excluido = 0 AND status = 1 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $usuario->email);
        $stmt->execute();
        
        $dados = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$dados) {
            return null;
        }else if(!password_verify($usuario->password, $dados['password'])) {
            return null;
        }
        
        $usuario->preenche_usuario($dados);
        
        return true;
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
    
    public function buscarPorId(UsuarioModel $usuario) {
        $sql = "SELECT * FROM usuarios WHERE idUsuarios = :id AND excluido = 0 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $usuario->idUsuarios);
        $stmt->execute();
        
        $dados = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$dados) {
            return null;
        }
        
        return $usuario->preenche_usuario($dados);
        
    }
    
    public function buscarPorEmail(UsuarioModel $usuario) {
        $sql = "SELECT * FROM usuarios WHERE email = :email AND excluido = 0 AND status = 1 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $usuario->email);
        $stmt->execute();
        
        $dados = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$dados) {
            return null;
        }
        
        return $usuario->preenche_dados($dados);
    }
    
    public function atualizarSenha(UsuarioModel $usuario) {
        $sql = "UPDATE usuarios SET 
                password = :password,
                updated_at = NOW()
                WHERE idUsuarios = :id AND excluido = 0";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':password', $usuario->password);
        $stmt->bindValue(':id', $usuario->idUsuarios);
        
        return $stmt->execute();
    }

    public function buscarPorFuncao(int $role_id, int $id_parceiros): array
    {
        $usuariosArray = [];

        $sql = "SELECT * FROM usuarios WHERE role_id = :role_id AND excluido = 0 AND status = 1 AND parceiros_idparceiros = :id_parceiros";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':role_id', $role_id);
        $stmt->bindValue(':id_parcerios', $role_id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $usuarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($usuarios as $usuario) {
                $usuarioModel = new UsuarioModel();
                $usuarioModel->preenche_usuario($usuario);
                array_push($usuariosArray, $usuario);
            }
        }

        return $usuariosArray;
    }

    public function buscarTodosUsuarios(int $id_parceiros): array
    {
        $usuariosArray = [];

        $sql = "SELECT * FROM usuarios WHERE excluido = 0 AND status = 1 AND parceiros_idparceiros = :id_parceiros";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $usuarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($usuarios as $usuario) {
                $usuarioModel = new UsuarioModel();
                $usuarioModel->preenche_usuario($usuario);
                array_push($usuariosArray, $usuario);
            }
        }

        return $usuariosArray;
    }

    public function deletar(UsuarioModel $usuario) {
        $sql = "UPDATE usuarios SET excluido = 1, updated_at = NOW() WHERE idUsuarios = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $usuario->idUsuarios);
        return $stmt->execute();
    }
}
