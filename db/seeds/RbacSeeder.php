<?php


use Phinx\Seed\AbstractSeed;

class RbacSeeder extends AbstractSeed
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
        // Create default roles
        $roles = [
            [
                'name' => 'Administrador',
                'description' => 'Acesso completo ao sistema',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Gerente',
                'description' => 'Acesso gerencial ao sistema',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Usuário',
                'description' => 'Acesso básico ao sistema',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('roles')->insert($roles)->saveData();

        // Create permissions
        $permissions = [
            // Usuários
            [
                'name' => 'Listar Usuários',
                'slug' => 'usuarios.listar',
                'description' => 'Listar todos os usuários',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Ver Usuário',
                'slug' => 'usuarios.ver',
                'description' => 'Ver detalhes de um usuário',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Criar Usuário',
                'slug' => 'usuarios.criar',
                'description' => 'Criar um novo usuário',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Editar Usuário',
                'slug' => 'usuarios.editar',
                'description' => 'Editar um usuário existente',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Excluir Usuário',
                'slug' => 'usuarios.excluir',
                'description' => 'Excluir um usuário',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            
            // Orçamentos
            [
                'name' => 'Listar Orçamentos',
                'slug' => 'orcamentos.listar',
                'description' => 'Listar todos os orçamentos',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Ver Orçamento',
                'slug' => 'orcamentos.ver',
                'description' => 'Ver detalhes de um orçamento',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Criar Orçamento',
                'slug' => 'orcamentos.criar',
                'description' => 'Criar um novo orçamento',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Editar Orçamento',
                'slug' => 'orcamentos.editar',
                'description' => 'Editar um orçamento existente',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Excluir Orçamento',
                'slug' => 'orcamentos.excluir',
                'description' => 'Excluir um orçamento',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Aprovar Orçamento',
                'slug' => 'orcamentos.aprovar',
                'description' => 'Aprovar um orçamento',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('permissions')->insert($permissions)->saveData();

        // Assign all permissions to Admin role
        $adminRole = $this->fetchRow('SELECT id FROM roles WHERE name = "Administrador"');
        $allPermissions = $this->fetchAll('SELECT id FROM permissions');
        
        $rolePermissions = [];
        foreach ($allPermissions as $permission) {
            $rolePermissions[] = [
                'role_id' => $adminRole['id'],
                'permission_id' => $permission['id'],
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }
        
        $this->table('role_permissions')->insert($rolePermissions)->saveData();

        // Create default admin user
        $users = [
            [
                'nome' => 'Administrador',
                'email' => 'admin@orcamentos.com',
                'telefone' => '(00) 00000-0000',
                'status' => 1,
                'excluido' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role_id' => $adminRole['id'],
            ],
        ];
        
        $this->table('usuarios')->insert($users)->saveData();
    }
}
