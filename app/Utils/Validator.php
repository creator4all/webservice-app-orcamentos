<?php

namespace App\Utils;

class Validator {
    private function __construct() 
    {
    }
    
    public static function validate(array $dados, array $camposObrigatorios) {
        $erros = [];
        
        foreach ($camposObrigatorios as $campo) {
            if (empty($dados[$campo])) {
                $erros[$campo] = "O campo $campo é obrigatório.";
            }
        }
        
        return [
            'valido' => empty($erros),
            'erros' => $erros
        ];
    }
}
