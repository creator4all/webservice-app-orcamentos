<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateParceirosAtualizacoesLogTable extends AbstractMigration
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
        if(!$this->hasTable('parceiros_atualizacoes_log')){
            $table = $this->table('parceiros_atualizacoes_log', ['id' => 'id', 'signed' => false]);
            $table->addColumn('parceiro_id', 'integer', ['signed' => false])
                  ->addColumn('usuario_id', 'integer', ['signed' => false])
                  ->addColumn('campo_atualizado', 'string', ['limit' => 45])
                  ->addColumn('valor_anterior', 'text', ['null' => true])
                  ->addColumn('valor_novo', 'text', ['null' => true])
                  ->addColumn('data_atualizacao', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                  ->addForeignKey('parceiro_id', 'parceiros', 'idparceiros', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                  ->create();
        }
    }
}
