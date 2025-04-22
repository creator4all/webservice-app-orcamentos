<?php
namespace App\DAO;

use App\Model\ParceiroModel;
use App\DAO\Connection;

class ParceiroDAO extends Connection {
    private $pdo;

    public function __construct() {
        $this->pdo = Connection::db();
    }

    /**
     * Busca um parceiro pelo CNPJ
     * @param string $cnpj CNPJ do parceiro
     * @return ParceiroModel|null Retorna o parceiro encontrado ou null se não encontrar
     */
    public function buscarPorCNPJ($cnpj) {
        $sql = "SELECT * FROM parceiros WHERE cnpj = :cnpj LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':cnpj', $cnpj);
        $stmt->execute();
        
        $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$resultado) {
            return null;
        }
        
        return new ParceiroModel($resultado);
    }

    /**
     * Insere um novo parceiro no banco de dados
     * @param ParceiroModel $parceiro Parceiro a ser inserido
     * @return int|false ID do parceiro inserido ou false em caso de erro
     */
    public function inserir(ParceiroModel $parceiro) {
        $sql = "INSERT INTO parceiros (cnpj, logomarca, nome_fantasia, razao_social, status, created_at, updated_at) 
                VALUES (:cnpj, :logomarca, :nome_fantasia, :razao_social, :status, NOW(), NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':cnpj', $parceiro->cnpj);
        $stmt->bindValue(':logomarca', $parceiro->logomarca);
        $stmt->bindValue(':nome_fantasia', $parceiro->nome_fantasia);
        $stmt->bindValue(':razao_social', $parceiro->razao_social);
        $stmt->bindValue(':status', $parceiro->status);
        
        if ($stmt->execute()) {
            return $this->pdo->lastInsertId();
        }
        
        return false;
    }
}
