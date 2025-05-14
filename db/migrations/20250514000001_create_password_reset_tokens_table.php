<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePasswordResetTokensTable extends AbstractMigration
{
    public function change(): void
    {
        if(!$this->hasTable('password_reset_tokens')){
            $table = $this->table('password_reset_tokens', ['id' => 'id', 'signed' => false]);
            $table->addColumn('usuarios_id', 'integer', ['signed' => false])
                  ->addColumn('token', 'string', ['limit' => 6])
                  ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('expires_at', 'datetime')
                  ->addColumn('used', 'boolean', ['default' => false])
                  ->addIndex(['token'])
                  ->addForeignKey('usuarios_id', 'usuarios', 'idUsuarios', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                  ->create();
        }
    }
}
