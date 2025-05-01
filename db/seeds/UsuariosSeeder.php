<?php

use Phinx\Seed\AbstractSeed;

class UsuariosSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run()
    {
        $adminRole = $this->fetchRow('SELECT id FROM roles WHERE name = "Administrador"');
        $gerenteRole = $this->fetchRow('SELECT id FROM roles WHERE name = "Gerente"');
        $usuarioRole = $this->fetchRow('SELECT id FROM roles WHERE name = "Usuário"');

        $parceiro1 = $this->fetchRow('SELECT idparceiros FROM parceiros WHERE cnpj = "12345678000190"');
        $parceiro2 = $this->fetchRow('SELECT idparceiros FROM parceiros WHERE cnpj = "98765432000110"');
        $parceiro3 = $this->fetchRow('SELECT idparceiros FROM parceiros WHERE cnpj = "45678912000134"');

        $plainPassword = "teste123";
        $testPasswordHash = password_hash($plainPassword, PASSWORD_DEFAULT);
        
        $timestamp = date('Y-m-d H:i:s');

        $users = [
            [
                'nome' => 'Gerente Teste',
                'email' => 'gerente@orcamentos.com',
                'telefone' => '(11) 98765-4321',
                'status' => 1,
                'excluido' => 0,
                'foto_perfil' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'parceiros_idparceiros' => $parceiro1['idparceiros'],
                'cargo' => 'Gerente de Projetos',
                'password' => $testPasswordHash,
                'remember_token' => null,
                'role_id' => $gerenteRole['id'],
            ],
            [
                'nome' => 'Usuário Teste 1',
                'email' => 'usuario1@orcamentos.com',
                'telefone' => '(21) 99876-5432',
                'status' => 1,
                'excluido' => 0,
                'foto_perfil' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'parceiros_idparceiros' => $parceiro1['idparceiros'],
                'cargo' => 'Analista',
                'password' => $testPasswordHash,
                'remember_token' => null,
                'role_id' => $usuarioRole['id'],
            ],
            [
                'nome' => 'Usuário Teste 2',
                'email' => 'usuario2@orcamentos.com',
                'telefone' => '(31) 97654-3210',
                'status' => 1,
                'excluido' => 0,
                'foto_perfil' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'parceiros_idparceiros' => $parceiro2['idparceiros'],
                'cargo' => 'Desenvolvedor',
                'password' => $testPasswordHash,
                'remember_token' => null,
                'role_id' => $usuarioRole['id'],
            ],
            [
                'nome' => 'Usuário Inativo',
                'email' => 'inativo@orcamentos.com',
                'telefone' => '(41) 98765-1234',
                'status' => 0,
                'excluido' => 0,
                'foto_perfil' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'parceiros_idparceiros' => $parceiro2['idparceiros'],
                'cargo' => 'Analista',
                'password' => $testPasswordHash,
                'remember_token' => null,
                'role_id' => $usuarioRole['id'],
            ],
            [
                'nome' => 'Admin Teste',
                'email' => 'admin2@orcamentos.com',
                'telefone' => '(51) 99876-4321',
                'status' => 1,
                'excluido' => 0,
                'foto_perfil' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'parceiros_idparceiros' => $parceiro3['idparceiros'],
                'cargo' => 'Administrador de Sistema',
                'password' => $testPasswordHash,
                'remember_token' => null,
                'role_id' => $adminRole['id'],
            ],
        ];
        
        $this->table('usuarios')->insert($users)->saveData();
    }
    
    /**
     * Specify that RbacSeeder and ParceiroSeeder must run before this seed
     */
    public function getDependencies()
    {
        return [
            'RbacSeeder',
            'ParceiroSeeder'
        ];
    }
}
