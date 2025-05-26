<?php
namespace App\DAO;

use App\Model\ParceiroModel;

class ParceiroDAO{
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Busca um parceiro pelo CNPJ
     * @param string $cnpj CNPJ do parceiro
     * @return ParceiroModel|null Retorna o parceiro encontrado ou null se não encontrar
     */
    public function buscarPorCNPJ(ParceiroModel $parceiro) {
        $sql = "SELECT * FROM parceiros WHERE cnpj = :cnpj LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':cnpj', $parceiro->cnpj);
        $stmt->execute();
        
        $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$resultado) {
            return null;
        }
        
        return $parceiro->preenche_parceiro($resultado);
    }

    /**
     * Insere um novo parceiro no banco de dados
     * @param ParceiroModel $parceiro Parceiro a ser inserido
     * @return int|false ID do parceiro inserido ou false em caso de erro
     */
    public function inserir(ParceiroModel $parceiro) {
        $sql = "INSERT INTO parceiros (cnpj, logomarca, nome_fantasia, razao_social, status, gestor_cadastrado, url, created_at, updated_at) 
                VALUES (:cnpj, :logomarca, :nome_fantasia, :razao_social, :status, :gestor_cadastrado, :url, NOW(), NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':cnpj', $parceiro->cnpj);
        $stmt->bindValue(':logomarca', $parceiro->logomarca);
        $stmt->bindValue(':nome_fantasia', $parceiro->nome_fantasia);
        $stmt->bindValue(':razao_social', $parceiro->razao_social);
        $stmt->bindValue(':status', $parceiro->status);
        $stmt->bindValue(':gestor_cadastrado', $parceiro->gestor_cadastrado);
        $stmt->bindValue(':url', $parceiro->url);
        
        if ($stmt->execute()) {
            return $this->pdo->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Atualiza um parceiro existente
     * @param ParceiroModel $parceiro Parceiro a ser atualizado
     * @return bool Sucesso ou falha da operação
     */
    public function atualizar(ParceiroModel $parceiro) {
        $sql = "UPDATE parceiros SET 
                nome_fantasia = :nome_fantasia, 
                razao_social = :razao_social, 
                logomarca = :logomarca, 
                url = :url,
                updated_by = :updated_by,
                updated_at = NOW()
                WHERE idparceiros = :id";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome_fantasia', $parceiro->nome_fantasia);
        $stmt->bindValue(':razao_social', $parceiro->razao_social);
        $stmt->bindValue(':logomarca', $parceiro->logomarca);
        $stmt->bindValue(':url', $parceiro->url);
        $stmt->bindValue(':updated_by', $parceiro->updated_by);
        $stmt->bindValue(':id', $parceiro->idparceiros);
        
        return $stmt->execute();
    }
}
