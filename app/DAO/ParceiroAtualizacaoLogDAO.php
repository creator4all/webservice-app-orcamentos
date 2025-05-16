<?php

namespace App\DAO;

use App\Model\ParceiroAtualizacaoLogModel;
use PDO;

class ParceiroAtualizacaoLogDAO extends DAO {
    
    /**
     * Registra uma atualização de parceiro no log
     * @param ParceiroAtualizacaoLogModel $log
     * @return bool
     */
    public function registrar(ParceiroAtualizacaoLogModel $log): bool {
        $sql = "INSERT INTO parceiros_atualizacoes_log 
                (parceiro_id, usuario_id, campo_atualizado, valor_anterior, valor_novo) 
                VALUES (:parceiro_id, :usuario_id, :campo_atualizado, :valor_anterior, :valor_novo)";
        
        $stmt = $this->pdo->prepare($sql);
        
        $stmt->bindValue(':parceiro_id', $log->parceiro_id, PDO::PARAM_INT);
        $stmt->bindValue(':usuario_id', $log->usuario_id, PDO::PARAM_INT);
        $stmt->bindValue(':campo_atualizado', $log->campo_atualizado, PDO::PARAM_STR);
        $stmt->bindValue(':valor_anterior', $log->valor_anterior, PDO::PARAM_STR);
        $stmt->bindValue(':valor_novo', $log->valor_novo, PDO::PARAM_STR);
        
        return $stmt->execute();
    }
    
    /**
     * Busca logs de atualizações de um parceiro específico
     * @param int $parceiroId
     * @return array
     */
    public function buscarPorParceiroId(int $parceiroId): array {
        $sql = "SELECT l.*, u.nome as nome_usuario 
                FROM parceiros_atualizacoes_log l
                LEFT JOIN usuarios u ON l.usuario_id = u.idUsuarios
                WHERE l.parceiro_id = :parceiro_id
                ORDER BY l.data_atualizacao DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':parceiro_id', $parceiroId, PDO::PARAM_INT);
        $stmt->execute();
        
        $logs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $logs[] = new ParceiroAtualizacaoLogModel($row);
        }
        
        return $logs;
    }
}
