<?php
namespace App\Model;

class UsuarioModel {
    public $idUsuarios;
    public $nome;
    public $email;
    public $telefone;
    public $status;
    public $excluido;
    public $foto_perfil;
    public $data_nascimento;
    public $created_at;
    public $updated_at;
    public $parceiros_idparceiros;
    public $cargos_idcargos;
    public $password;
    public $role_id;

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            if(property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
