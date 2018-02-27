<?php


use Phinx\Migration\AbstractMigration;

class ResetCode extends AbstractMigration
{
    /**
     * Table for password reset codes
     */
    public function change()
    {
        $table = $this->table('reset_codes');
        $table->addColumn('uuid', 'string')
            ->addColumn('code', 'text')
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('expiration', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['uuid'], ['unique' => true])
            ->addForeignKey('uuid', 'users', 'uuid', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->create();
    }
}
