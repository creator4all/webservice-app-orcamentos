<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddGestorCadastradoToParceiros extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('parceiros');
        $table->addColumn('gestor_cadastrado', 'boolean', ['default' => false])
              ->update();
    }
}
