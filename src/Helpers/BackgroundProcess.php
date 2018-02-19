<?php
/**
 * Run and monitor a command in a background process
 */

namespace Nucleus\Helpers;

class BackgroundProcess
{
    private $command;
    private $pid;
    private $tag = null;

    /**
     * Define the command to be run
     * @param $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * Execute the command
     * @param string $outputFile
     */
    public function run($outputFile = '/dev/null')
    {
        $this->pid = shell_exec(sprintf(
            '%s > %s 2>&1 & echo $!',
            $this->command,
            $outputFile
        ));
    }

    /**
     * Check if the executed command is currently running
     * @return bool
     */
    public function isRunning()
    {
        try {
            $result = shell_exec(sprintf('ps %d', $this->pid));
            if (count(preg_split("/\n/", $result)) > 2) {
                return true;
            }
        } catch (Exception $e) {
        }

        return false;
    }

    /**
     * Return the pid after removing excess garbage
     * @return mixed
     */
    public function getPid()
    {
        $p = explode("\n", $this->pid);
        return($p[0]);
    }

    /**
     * Set a human readable tag
     * @param null $tag
     */
    public function setTag($tag = null)
    {
        $this->tag = $tag;
    }

    /**
     * Fetch the human readable tag
     * @return null
     */
    public function getTag()
    {
        return $this->tag;
    }
}
