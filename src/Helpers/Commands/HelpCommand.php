<?php

namespace Nucleus\Helpers\Commands;

class HelpCommand extends BaseCommand
{
    /**
     * Display top level help
     * @param $arguments
     */
    public function command($arguments)
    {
        // Define arguments
        $this->cli->arguments->add([
            'db' => [
                'description' => 'Phinx database migrations'
            ],
            'db:setup' => [
                'description' => 'Run pre-defined migration and seeders'
            ],
            'test' => [
                'description' => 'Execute PHPUnit'
            ],
            'cs' => [
                'description' => 'PHP Code Sniffer test'
            ],
            'csfix' => [
                'description' => 'PHP Code Sniffer auto-repair'
            ],
            'build' => [
                'description' => 'Shortcut for build:css, build:js, build:assets, and build:deploy'
            ],
            'build:css' => [
                'description' => 'Combine and minify CSS files for active template'
            ],
            'build:js' => [
                'description' => 'Combine and minify Javascript files for active template'
            ],
            'build:assets' => [
                'description' => 'Collect and copy visual assets for active template'
            ],
            'build:deploy' => [
                'description' => 'Deploy active template'
            ],
            'user:create' => [
                'description' => 'Create a new user'
            ],
            'user:edit' => [
                'description' => 'Edit an existing user'
            ],
            'help' => [
                'prefix' => 'h',
                'longPrefix'  => 'help',
                'description' => 'Prints a usage statement',
                'noValue'     => true
            ]
        ]);

        $this->cli->out("\n")->usage();
        $this->cli->out("\n");

    }
}
