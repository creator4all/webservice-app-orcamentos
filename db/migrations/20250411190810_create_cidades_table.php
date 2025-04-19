<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCidadesTable extends AbstractMigration
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
        // Create cidades (cities) table
        $table = $this->table('cidades', ['id' => 'idcidades', 'signed' => false]);
        $table->addColumn('nome', 'string', ['limit' => 45])
              ->addColumn('regiao', 'string', ['limit' => 45, 'null' => true])
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->addColumn('estados_idestados', 'integer', ['signed' => false])
              ->addForeignKey('estados_idestados', 'estados', 'idestados', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->create();
    }
}
