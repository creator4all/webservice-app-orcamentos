<?php
namespace App\Utils;

class CNPJValidator {
    /**
     * Valida o formato do CNPJ (apenas formato, não verifica se é um CNPJ real)
     * @param string $cnpj CNPJ a ser validado
     * @return bool Retorna true se o formato for válido
     */
    public static function validarFormato($cnpj) {
        // Remove caracteres não numéricos
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        // Verifica se tem 14 dígitos
        if (strlen($cnpj) != 14) {
            return false;
        }
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(\d)\1+$/', $cnpj)) {
            return false;
        }
        
        // Validação do dígito verificador
        $soma = 0;
        $multiplicador = 5;
        
        // Primeiro dígito verificador
        for ($i = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $multiplicador;
            $multiplicador = ($multiplicador == 2) ? 9 : $multiplicador - 1;
        }
        
        $resto = $soma % 11;
        $dv1 = ($resto < 2) ? 0 : 11 - $resto;
        
        if ($dv1 != $cnpj[12]) {
            return false;
        }
        
        // Segundo dígito verificador
        $soma = 0;
        $multiplicador = 6;
        
        for ($i = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $multiplicador;
            $multiplicador = ($multiplicador == 2) ? 9 : $multiplicador - 1;
        }
        
        $resto = $soma % 11;
        $dv2 = ($resto < 2) ? 0 : 11 - $resto;
        
        return ($dv2 == $cnpj[13]);
    }
    
    /**
     * Formata um CNPJ para o formato padrão XX.XXX.XXX/XXXX-XX
     * @param string $cnpj CNPJ a ser formatado
     * @return string CNPJ formatado
     */
    public static function formatar($cnpj) {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) != 14) {
            return $cnpj;
        }
        
        return sprintf(
            '%s.%s.%s/%s-%s',
            substr($cnpj, 0, 2),
            substr($cnpj, 2, 3),
            substr($cnpj, 5, 3),
            substr($cnpj, 8, 4),
            substr($cnpj, 12, 2)
        );
    }
    
    /**
     * Sanitiza um CNPJ removendo caracteres não numéricos
     * @param string $cnpj CNPJ a ser sanitizado
     * @return string CNPJ apenas com números
     */
    public static function sanitizar($cnpj) {
        return preg_replace('/[^0-9]/', '', $cnpj);
    }
}
