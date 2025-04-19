<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateRbacTables extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        if($this->hasTable('roles')){
            // Create roles table
            $roles = $this->table('roles', ['id' => 'id', 'signed' => false]);
            $roles->addColumn('name', 'string', ['limit' => 50])
                  ->addColumn('description', 'string', ['limit' => 255, 'null' => true])
                  ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'datetime', ['null' => true])
                  ->addIndex(['name'], ['unique' => true])
                  ->create();
        }

        if($this->hasTable('permissions')){
            // Create permissions table
            $permissions = $this->table('permissions', ['id' => 'id', 'signed' => false]);
            $permissions->addColumn('name', 'string', ['limit' => 50])
                        ->addColumn('slug', 'string', ['limit' => 50])
                        ->addColumn('description', 'string', ['limit' => 255, 'null' => true])
                        ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                        ->addColumn('updated_at', 'datetime', ['null' => true])
                        ->addIndex(['slug'], ['unique' => true])
                        ->create();
        }

        if($this->hasTable('role_permissions')){
            // Create role_permissions pivot table
            $rolePermissions = $this->table('role_permissions', ['id' => false, 'primary_key' => ['role_id', 'permission_id']]);
            $rolePermissions->addColumn('role_id', 'integer', ['signed' => false])
                            ->addColumn('permission_id', 'integer', ['signed' => false])
                            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                            ->addForeignKey('role_id', 'roles', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                            ->addForeignKey('permission_id', 'permissions', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                            ->create();
                            // Add foreign key to users table for role_id
            $this->table('usuarios')
            ->addForeignKey('role_id', 'roles', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->update();
        }

        if($this->hasTable('user_permissions')){
            // Create user_permissions pivot table for direct permission assignments
            $userPermissions = $this->table('user_permissions', ['id' => false, 'primary_key' => ['user_id', 'permission_id']]);
            $userPermissions->addColumn('user_id', 'integer', ['signed' => false])
                            ->addColumn('permission_id', 'integer', ['signed' => false])
                            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                            ->addForeignKey('user_id', 'usuarios', 'idUsuarios', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                            ->addForeignKey('permission_id', 'permissions', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                            ->create();
        }
    }
}
