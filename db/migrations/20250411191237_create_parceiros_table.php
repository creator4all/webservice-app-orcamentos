<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateParceirosTable extends AbstractMigration
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
        if(!$this->hasTable('parceiros')){
            // Create parceiros (partners) table
            $table = $this->table('parceiros', ['id' => 'idparceiros', 'signed' => false]);
            $table->addColumn('cnpj', 'string', ['limit' => 45])
                  ->addColumn('logomarca', 'string', ['limit' => 45, 'null' => true])
                  ->addColumn('nome_fantasia', 'string', ['limit' => 45])
                  ->addColumn('razao_social', 'string', ['limit' => 45])
                  ->addColumn('status', 'boolean', ['default' => true])
                  ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                  ->addIndex(['cnpj'], ['unique' => true])
                  ->create();
        }
    }
}
