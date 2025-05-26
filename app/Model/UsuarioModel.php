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
            if(property_exists($this, "password")){
                $this->setPassword($value);
            }else if(property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function preenche_usuario(array $data) {
        foreach ($data as $key => $value) {
            if(property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function setPassword($password){
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }


    public function isAdmin(): bool {
        return $this->role_id === 1;
    }
    
    public function isGestor(): bool {
        return $this->role_id === 2;
    }

    public function isVendedor(): bool {
        return $this->role_id === 3;
    }
    
}
