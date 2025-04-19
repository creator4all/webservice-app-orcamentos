<?php
namespace App\Model;

class ParceiroModel {
    public $idparceiros;
    public $cnpj;
    public $logomarca;
    public $nome_fantasia;
    public $razao_social;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            if(property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Verifica se o parceiro está ativo
     * @return bool Retorna true se o parceiro está ativo
     */
    public function isAtivo() {
        return $this->status == true;
    }
}
