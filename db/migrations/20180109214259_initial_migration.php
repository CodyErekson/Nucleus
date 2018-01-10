<?php


use Phinx\Migration\AbstractMigration;

class InitialMigration extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
		$table = $this->table('users', ['id' => false, 'primary_key' => 'uuid']);
		$table->addColumn('uuid', 'string')
			->addColumn('username', 'string')
			->addColumn('email', 'string')
			->addColumn('password', 'string')
			->addColumn('created_at', 'datetime')
			->addColumn('updated_at', 'datetime')
			->addIndex(['username', 'email'], ['unique' => true])
			->create();

		$table = $this->table('tokens');
		$table->addColumn('uuid', 'string')
			->addColumn('token', 'string')
			->addColumn('created_at', 'datetime')
			->addColumn('updated_at', 'datetime')
			->addColumn('expiration', 'datetime')
			->create();
    }
}
