<?php

namespace App\Model;

class ParceiroAtualizacaoLogModel {
    public $id;
    public $parceiro_id;
    public $usuario_id;
    public $campo_atualizado;
    public $valor_anterior;
    public $valor_novo;
    public $data_atualizacao;
    
    public function __construct(array $data = []) {
        $this->id = $data['id'] ?? null;
        $this->parceiro_id = $data['parceiro_id'] ?? null;
        $this->usuario_id = $data['usuario_id'] ?? null;
        $this->campo_atualizado = $data['campo_atualizado'] ?? null;
        $this->valor_anterior = $data['valor_anterior'] ?? null;
        $this->valor_novo = $data['valor_novo'] ?? null;
        $this->data_atualizacao = $data['data_atualizacao'] ?? null;
    }
}
