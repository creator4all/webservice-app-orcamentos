<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCensoTable extends AbstractMigration
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
        // Create censo (census) table
        $table = $this->table('censo', ['id' => 'idcenso', 'signed' => false]);
        $table->addColumn('bercario', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('maternal', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('in4anos', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('in5anos', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef1ano', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef2ano', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef3ano', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef4ano', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef5ano', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef6ano', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef7ano', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef8ano', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef9ano', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('em1ano', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('em2ano', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('em3ano', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('bercarioP', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('maternalP', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('in4anosP', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('in5anosP', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef1anoP', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef2anoP', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef3anoP', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef4anoP', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef5anoP', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef6anoP', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef7anoP', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef8anoP', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('ef9anoP', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('em1anoP', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('em2anoP', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('em3anoP', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('cidades_idcidades', 'integer', ['signed' => false])
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->addForeignKey('cidades_idcidades', 'cidades', 'idcidades', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->addIndex(['cidades_idcidades'], ['unique' => true])
              ->create();
    }
}
