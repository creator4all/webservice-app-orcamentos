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


}
