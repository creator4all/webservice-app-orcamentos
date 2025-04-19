<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
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
        // Create users table
        $table = $this->table('usuarios', ['id' => 'idUsuarios', 'signed' => false]);
        $table->addColumn('nome', 'string', ['limit' => 45])
              ->addColumn('email', 'string', ['limit' => 45])
              ->addColumn('telefone', 'string', ['limit' => 45, 'null' => true])
              ->addColumn('status', 'boolean', ['default' => true])
              ->addColumn('excluido', 'boolean', ['default' => false])
              ->addColumn('foto_perfil', 'string', ['limit' => 45, 'null' => true])
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->addColumn('parceiros_idparceiros', 'integer', ['null' => true, 'signed' => false])
              ->addColumn('cargo', 'string', ['null' => false, 'limit' => 45])
              ->addColumn('password', 'string', ['limit' => 255])
              ->addColumn('remember_token', 'string', ['limit' => 100, 'null' => true])
              ->addColumn('role_id', 'integer', ['signed' => false, 'null' => true])
              ->addIndex(['email'], ['unique' => true])
              ->addForeignKey('parceiros_idparceiros', 'parceiros', 'idparceiros', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
              ->create();
    }
}
