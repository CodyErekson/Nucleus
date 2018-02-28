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
                "env" => true
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
            ],
            [
                "setting" => "VALIDATE_NEW_ACCOUNT",
                "value" => false,
                "env" => true
            ],
            [
                "setting" => "MAIL_TRANSPORT",
                "value" => "SMTP", // or SENDMAIL
                "env" => true
            ],
            [
                "setting" => "SMTP_SERVER",
                "value" => "192.168.1.76",
                "env" => true,
                "allow_null" => true
            ],
            [
                "setting" => "SMTP_PORT",
                "value" => "1025",
                "env" => true,
                "allow_null" => true
            ],
            [
                "setting" => "APP_EMAIL_ADDRESS",
                "value" =>  "nucleus@nucleus.com",
                "env" => true
            ],
            [
                "setting" => "RESET_CODE_LIFETIME",
                "value" => 24,
                "env" => true
            ]
        ];
        $table = $this->table('settings');
        $table->insert($settings)
            ->save();

    }
}
