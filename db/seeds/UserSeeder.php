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
				'username' => 'Cody',
				'email' => 'cody@erekson.org',
				'password' => 'fd1f4b15cb78273c37e626ec1b69b1e4',
				'created_at' => date('Y-m-d H:i:s'),
			],
			[
				'username' => 'Bob',
				'email' => 'bob@gmail.com',
				'password' => 'e93ac9aef629bb0ea82166a6907a9a4d', //iambob
				'created_at' => date('Y-m-d H:i:s'),
			],
			[
				'username' => 'Jim',
				'email' => 'jim@yahoo.com',
				'password' => 'ecf4fbbeb03457c68c4d4ca38cac73e6', //iamjim
				'created_at' => date('Y-m-d H:i:s'),
			]
		];
		$table = $this->table('users');
		$table->insert($users)
			->save();
    }
}
