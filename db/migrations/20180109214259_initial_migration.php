<?php


use Phinx\Migration\AbstractMigration;

class InitialMigration extends AbstractMigration
{
    /**
     * Create all database tables necessary for Nucleus to function
	 * Insert 3 required roles
     */
    public function change()
    {
		$table = $this->table('users', ['id' => false, 'primary_key' => 'uuid']);
		$table->addColumn('uuid', 'string')
			->addColumn('username', 'string')
			->addColumn('email', 'string')
			->addColumn('password', 'string')
			->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
			->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
			->addColumn('active', 'boolean', ['default' => 1])
			->addIndex(['username'], ['unique' => true])
			->addIndex(['email'], ['unique' => true])

			->create();

		$table = $this->table('tokens');
		$table->addColumn('uuid', 'string')
			->addColumn('token', 'text')
			->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
			->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
			->addColumn('expiration', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
			->addForeignKey('uuid', 'users', 'uuid', ['delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'])
			->create();

		$table = $this->table('roles');
		$table->addColumn('role', 'string')
			->create();

		// Add roles
		$roles = [
			[
				'role' => 'guest'
			],
			[
				'role' => 'member'
			],
			[
				'role' => 'admin'
			]
		];
		$table->insert($roles);
		$table->saveData();

		$table =$this->table('role_user');
		$table->addColumn('user_uuid', 'string')
			->addColumn('role_id', 'integer')
			->addForeignKey('user_uuid', 'users', 'uuid', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
			->addForeignKey('role_id', 'roles', 'id', ['delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'])
			->create();
    }
}
