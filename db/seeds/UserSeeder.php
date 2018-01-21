<?php


use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    /**
     * Create a few new users
     */
    public function run()
	{

		// Create some users
		$users = [
			[
				'uuid' => Ramsey\Uuid\Uuid::uuid4()->toString(),
				'username' => 'Cody',
				'email' => 'cody@erekson.org',
				'password' => password_hash('yesiamcody', PASSWORD_BCRYPT), //iamcody
				'created_at' => date('Y-m-d H:i:s'),
			],
			[
				'uuid' => Ramsey\Uuid\Uuid::uuid4()->toString(),
				'username' => 'Bobby',
				'email' => 'bob@gmail.com',
				'password' => password_hash('yesiambobby', PASSWORD_BCRYPT), //iambob
				'created_at' => date('Y-m-d H:i:s'),
			],
			[
				'uuid' => Ramsey\Uuid\Uuid::uuid4()->toString(),
				'username' => 'Jimmy',
				'email' => 'jim@yahoo.com',
				'password' => password_hash('yesiamjimmy', PASSWORD_BCRYPT), //iamjim
				'created_at' => date('Y-m-d H:i:s'),
			]
		];
		$table = $this->table('users');
		$table->insert($users)
			->save();
	}
}
