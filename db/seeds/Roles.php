<?php


use Phinx\Seed\AbstractSeed;

class Roles extends AbstractSeed
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
        $roles = [
            [
                'role' => 'guest'
            ],
            [
                'role' => 'member'
            ],
            [
                'role' => 'moderator'
            ],
            [
                'role' => 'admin'
            ]
        ];
        $table = $this->table('roles');
        $table->insert($roles);
        $table->saveData();
    }
}