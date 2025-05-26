<?php
namespace App\DAO;

use App\Model\PasswordResetTokenModel;

class PasswordResetTokenDAO {
    private $pdo;

    public function __construct(\PDO $pdo){
        $this->pdo = $pdo;
    }

    public function criar(PasswordResetTokenModel $token) {
        $sql = "INSERT INTO password_reset_tokens (usuarios_id, token, created_at, expires_at, used) 
                VALUES (:usuarios_id, :token, NOW(), :expires_at, :used)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':usuarios_id', $token->usuarios_id);
        $stmt->bindValue(':token', $token->token);
        $stmt->bindValue(':expires_at', $token->expires_at);
        $stmt->bindValue(':used', $token->used, \PDO::PARAM_BOOL);
        return $stmt->execute();
    }

    public function buscarPorToken(PasswordResetTokenModel $token) {
        $sql = "SELECT * FROM password_reset_tokens WHERE token = :token ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':token', $token);
        $stmt->execute();
        
        $dados = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$dados) {
            return null;
        }
        
        return $token->preenche_token($dados);
    }

    public function marcarComoUsado(PasswordResetTokenModel $token) {
        $sql = "UPDATE password_reset_tokens SET used = TRUE WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $token->id);
        return $stmt->execute();
    }

    public function invalidarTokensAnteriores($usuarios_id) {
        $sql = "UPDATE password_reset_tokens SET used = TRUE WHERE usuarios_id = :usuarios_id AND used = FALSE";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':usuarios_id', $usuarios_id);
        return $stmt->execute();
    }
}
