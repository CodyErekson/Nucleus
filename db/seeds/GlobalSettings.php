<?php


use Phinx\Seed\AbstractSeed;

class GlobalSettings extends AbstractSeed
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
        $settings = [
            [
                "setting" => "TIMEZONE",
                "value" => (date_default_timezone_get() == "UTC") ? "America/Denver" : date_default_timezone_get(),
                "env" => 1
            ],
            [
                "setting" => "NETSUITE_RECORDS_THRESHOLD",
                "value" => 95
            ],
            [
                "setting" => "IP_WHITELIST",
                "value" => "192.168.1.1",
                "allow_null" => true
            ],
            [
                "setting" => "IP_BLACKLIST",
                "value" => "",
                "allow_null" => true
            ]
        ];
        $table = $this->table('settings');
        $table->insert($settings)
            ->save();

    }
}
