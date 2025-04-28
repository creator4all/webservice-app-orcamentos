<?php
namespace App\Utils;

class InputSanitizer {
    /**
     * Sanitiza uma string removendo tags HTML/PHP e caracteres perigosos
     */
    public static function sanitizeString($string) {
        if (!is_string($string)) {
            return '';
        }
        $string = trim($string);
        $string = strip_tags($string);
        $string = preg_replace('/<\?php.*?\?>/is', '', $string); // Remove possíveis tags PHP
        $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        return $string;
    }

    /**
     * Valida se um email está em formato válido
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Valida se uma senha atende aos requisitos mínimos
     */
    public static function validatePassword($password) {
        // Pelo menos 6 caracteres
        return is_string($password) && strlen($password) >= 6;
    }

    /**
     * Sanitiza todos os valores string em um array
     */
    public static function sanitizeArray(array $dados) {
        $sanitized = [];
        foreach ($dados as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = self::sanitizeString($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Valida os dados de um usuário
     * @return array Array vazio se não houver erros, ou array com erros encontrados
     */
    public static function validateUsuario(array $dados) {
        $erros = [];
        
        // Validação de nome
        if (empty($dados['nome'])) {
            $erros['nome'] = 'Nome é obrigatório.';
        } else if (strlen($dados['nome']) < 2) {
            $erros['nome'] = 'Nome deve ter pelo menos 2 caracteres.';
        }
        
        // Validação de email
        if (empty($dados['email'])) {
            $erros['email'] = 'E-mail é obrigatório.';
        } else if (!self::validateEmail($dados['email'])) {
            $erros['email'] = 'E-mail em formato inválido.';
        }
        
        // Validação de senha
        if (empty($dados['password'])) {
            $erros['password'] = 'Senha é obrigatória.';
        } else if (!self::validatePassword($dados['password'])) {
            $erros['password'] = 'Senha deve ter pelo menos 6 caracteres.';
        }
        
        return $erros;
    }
    
    /**
     * Valida se uma URL está em formato válido
     */
    public static function validateUrl($url) {
        if (empty($url)) {
            return true; // URL é opcional
        }
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
