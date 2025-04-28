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
    
    /**
     * Busca gestores e vendedores com paginação
     * @param int $parceiroId ID do parceiro (opcional)
     * @param bool $incluirEmpresa Se deve incluir informações da empresa
     * @param int $pagina Número da página
     * @param int $porPagina Itens por página
     * @return array Array com os usuários e informações de paginação
     */
    public function buscarGestoresEVendedores($parceiroId = null, $incluirEmpresa = false, $pagina = 1, $porPagina = 10) {
        $offset = ($pagina - 1) * $porPagina;
        
        $sqlSelect = "SELECT u.* ";
        $sqlFrom = " FROM usuarios u ";
        $sqlWhere = " WHERE u.excluido = 0 AND (u.role_id = 2 OR u.role_id = 3) "; // role_id 2 = Gerente, 3 = Vendedor
        $sqlParams = [];
        
        if ($parceiroId !== null) {
            $sqlWhere .= " AND u.parceiros_idparceiros = :parceiro_id ";
            $sqlParams[':parceiro_id'] = $parceiroId;
        }
        
        if ($incluirEmpresa) {
            $sqlSelect .= ", p.nome_fantasia, p.razao_social, p.cnpj, p.logomarca, p.url ";
            $sqlFrom .= " LEFT JOIN parceiros p ON u.parceiros_idparceiros = p.idparceiros ";
        }
        
        $sqlCount = "SELECT COUNT(*) as total" . $sqlFrom . $sqlWhere;
        $stmtCount = $this->pdo->prepare($sqlCount);
        foreach ($sqlParams as $param => $value) {
            $stmtCount->bindValue($param, $value);
        }
        $stmtCount->execute();
        $totalRegistros = $stmtCount->fetch(\PDO::FETCH_ASSOC)['total'];
        
        $sql = $sqlSelect . $sqlFrom . $sqlWhere . " ORDER BY u.nome LIMIT :offset, :limit";
        $stmt = $this->pdo->prepare($sql);
        foreach ($sqlParams as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $porPagina, \PDO::PARAM_INT);
        $stmt->execute();
        
        $usuarios = [];
        while ($dados = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $usuario = new UsuarioModel($dados);
            
            if ($incluirEmpresa && isset($dados['nome_fantasia'])) {
                $usuario->empresa = [
                    'nome_fantasia' => $dados['nome_fantasia'],
                    'razao_social' => $dados['razao_social'],
                    'cnpj' => $dados['cnpj'],
                    'logomarca' => $dados['logomarca'],
                    'url' => $dados['url']
                ];
            }
            
            $usuarios[] = $usuario;
        }
        
        $totalPaginas = ceil($totalRegistros / $porPagina);
        
        return [
            'usuarios' => $usuarios,
            'paginacao' => [
                'total' => $totalRegistros,
                'por_pagina' => $porPagina,
                'pagina_atual' => $pagina,
                'ultima_pagina' => $totalPaginas
            ]
        ];
    }
    
    /**
     * Busca apenas vendedores de um parceiro com paginação
     * @param int $parceiroId ID do parceiro
     * @param int $pagina Número da página
     * @param int $porPagina Itens por página
     * @return array Array com os usuários e informações de paginação
     */
    public function buscarVendedores($parceiroId, $pagina = 1, $porPagina = 10) {
        $offset = ($pagina - 1) * $porPagina;
        
        $sqlCount = "SELECT COUNT(*) as total FROM usuarios 
                     WHERE excluido = 0 
                     AND parceiros_idparceiros = :parceiro_id 
                     AND role_id = 3"; // role_id 3 = Vendedor
        
        $stmtCount = $this->pdo->prepare($sqlCount);
        $stmtCount->bindValue(':parceiro_id', $parceiroId);
        $stmtCount->execute();
        $totalRegistros = $stmtCount->fetch(\PDO::FETCH_ASSOC)['total'];
        
        $sql = "SELECT * FROM usuarios 
                WHERE excluido = 0 
                AND parceiros_idparceiros = :parceiro_id 
                AND role_id = 3
                ORDER BY nome LIMIT :offset, :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':parceiro_id', $parceiroId);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $porPagina, \PDO::PARAM_INT);
        $stmt->execute();
        
        $usuarios = [];
        while ($dados = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $usuarios[] = new UsuarioModel($dados);
        }
        
        $totalPaginas = ceil($totalRegistros / $porPagina);
        
        return [
            'usuarios' => $usuarios,
            'paginacao' => [
                'total' => $totalRegistros,
                'por_pagina' => $porPagina,
                'pagina_atual' => $pagina,
                'ultima_pagina' => $totalPaginas
            ]
        ];
    }
}
