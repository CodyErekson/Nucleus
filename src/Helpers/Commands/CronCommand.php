<?php

namespace Nucleus\Helpers\Commands;

class CronCommand extends BaseCommand
{
    /**
     * Run command via cron
     * @param $arguments
     */
    public function command($arguments)
    {
        $this->cli->arguments->add([
            'command' => [
                'prefix' => 'c',
                'longPrefix' => 'command',
                'description' => 'Command to execute',
                'required' => true
            ],
            'tag' => [
                'prefix' => 't',
                'longPrefix' => 'tag',
                'description' => 'Tag to assign to process',
            ],
            'help' => [
                'longPrefix'  => 'help',
                'description' => 'Prints a usage statement',
                'noValue'     => true,
            ]
        ]);

        try {
            $this->cli->arguments->parse();
        } catch (\Exception $e) {
            $this->cli->out("\n" . $e->getMessage() . "\n");
            $this->cli->usage();
            exit();
        }

        if ($this->cli->arguments->defined('help')) {
            $this->cli->usage();
            exit();
        }

        // Run the command
        $processes = [];

        $p = $this->container->background_process;
        $p->setTag($this->cli->arguments->get('tag'));
        $p->setCommand($this->cli->arguments->get('command'));
        $p->run();
        $processes[$p->getPid()] = $p;

        // let's delay a few seconds, then report back which ones are running
        sleep(2);
        foreach ($processes as $pid) {
            $this->container->cli->yellow()->inline("\n" . $pid->getTag());
            $this->container->cli->white()->inline(" " . $pid->getPid());
            if ($pid->isRunning()) {
                $this->container->cli->green()->out(" running");
            } else {
                $this->container->cli->red()->out(" stopped");
            }
        }
    }
}
