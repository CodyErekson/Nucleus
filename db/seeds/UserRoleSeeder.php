<?php


use Phinx\Seed\AbstractSeed;

class UserRoleSeeder extends AbstractSeed
{
    /**
     * Assign some roles to the newly created users
     */
    public function run()
    {
    	$rows = $this->fetchAll('SELECT uuid, username FROM users');

    	$users = [];
    	foreach($rows as $row){
    		$users[$row['username']] = $row['uuid'];
		}

		// Assign roles
		$user_roles = [
			[
				'user_uuid' => $users['Cody'],
				'role_id' => 2
			],
			[
				'user_uuid' => $users['Bobby'],
				'role_id' => 2
			],
			[
				'user_uuid' => $users['Jimmy'],
				'role_id' => 2
			],
			[
				'user_uuid' => $users['Cody'],
				'role_id' => 3
			]
		];
		$table = $this->table('role_user');
		$table->insert($user_roles)
			->save();
    }
}
