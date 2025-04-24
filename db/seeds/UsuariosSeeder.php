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

        $prefix = "orcamentos_";
        $plainPassword = "teste123";
        $testPasswordHash = password_hash($prefix . $plainPassword, PASSWORD_DEFAULT);
        
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
                'parceiros_idparceiros' => null,
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
                'parceiros_idparceiros' => null,
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
                'parceiros_idparceiros' => null,
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
                'parceiros_idparceiros' => null,
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
                'parceiros_idparceiros' => null,
                'cargo' => 'Administrador de Sistema',
                'password' => $testPasswordHash,
                'remember_token' => null,
                'role_id' => $adminRole['id'],
            ],
        ];
        
        $this->table('usuarios')->insert($users)->saveData();
    }
    
    /**
     * Specify that RbacSeeder must run before this seed
     */
    public function getDependencies()
    {
        return [
            'RbacSeeder'
        ];
    }
}
