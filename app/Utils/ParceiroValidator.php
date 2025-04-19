<?php
namespace App\Utils;

use App\DAO\ParceiroDAO;
use App\Model\ParceiroModel;

class ParceiroValidator {
    /**
     * Valida um CNPJ e verifica se existe um parceiro ativo com este CNPJ
     * @param string $cnpj CNPJ a ser validado
     * @param ParceiroDAO $parceiroDAO Instância do DAO de parceiros
     * @return array Retorna array com 'valido', 'mensagem' e 'parceiro' (se válido)
     */
    public static function validarCNPJParceiro($cnpj, ParceiroDAO $parceiroDAO) {
        // Sanitizar e validar formato do CNPJ
        $cnpj = CNPJValidator::sanitizar($cnpj);
        
        if (!CNPJValidator::validarFormato($cnpj)) {
            return [
                'valido' => false,
                'mensagem' => 'Formato de CNPJ inválido.'
            ];
        }
        
        // Buscar parceiro pelo CNPJ
        $parceiro = $parceiroDAO->buscarPorCNPJ($cnpj);
        
        if (!$parceiro) {
            return [
                'valido' => false,
                'mensagem' => 'Parceiro não encontrado com este CNPJ.'
            ];
        }
        
        // Verificar se o parceiro está ativo
        if (!$parceiro->isAtivo()) {
            return [
                'valido' => false,
                'mensagem' => 'Parceiro encontrado, mas está inativo.'
            ];
        }
        
        return [
            'valido' => true,
            'parceiro' => $parceiro
        ];
    }
}
