<?php


use Phinx\Migration\AbstractMigration;

class GlobalSettings extends AbstractMigration
{
    /**
     * Table for global configuration
     */
    public function change()
    {
        $table = $this->table('settings');
        $table->addColumn('setting', 'string')
            ->addColumn('value', 'string', ['default' => null])
            ->addColumn('allow_null', 'boolean', ['default' => 0])
            ->addColumn('env', 'boolean', ['default' => 0])
            ->addIndex(['setting'], ['unique' => true])
            ->create();
    }
}
