<?php


use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {

		// Insert some initial data
		$users = [
			[
				'uuid' => Ramsey\Uuid\Uuid::uuid5(Ramsey\Uuid\Uuid::NAMESPACE_DNS, 'Cody')->toString(),
				'username' => 'Cody',
				'email' => 'cody@erekson.org',
				'password' => password_hash('iamcody', PASSWORD_BCRYPT), //iamcody
				'created_at' => date('Y-m-d H:i:s'),
			],
			[
				'uuid' => Ramsey\Uuid\Uuid::uuid5(Ramsey\Uuid\Uuid::NAMESPACE_DNS, 'Bob')->toString(),
				'username' => 'Bob',
				'email' => 'bob@gmail.com',
				'password' => password_hash('iambob', PASSWORD_BCRYPT), //iambob
				'created_at' => date('Y-m-d H:i:s'),
			],
			[
				'uuid' => Ramsey\Uuid\Uuid::uuid5(Ramsey\Uuid\Uuid::NAMESPACE_DNS, 'Jim')->toString(),
				'username' => 'Jim',
				'email' => 'jim@yahoo.com',
				'password' => password_hash('iamjim', PASSWORD_BCRYPT), //iamjim
				'created_at' => date('Y-m-d H:i:s'),
			]
		];
		$table = $this->table('users');
		$table->insert($users)
			->save();
    }
}
