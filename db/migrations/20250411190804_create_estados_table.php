<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateEstadosTable extends AbstractMigration
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
        if($this->hasTable('estados')){
            // Create estados (states) table
            $table = $this->table('estados', ['id' => 'idestados', 'signed' => false]);
            $table->addColumn('estado', 'string', ['limit' => 45])
                  ->addColumn('regiao', 'string', ['limit' => 45])
                  ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                  ->addIndex(['estado'], ['unique' => true])
                  ->create();
        }
    }
}
