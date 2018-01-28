<?php

namespace Nucleus\Helpers\Commands;

class TestCommand extends BaseCommand
{
    public function command($arguments)
    {
        $this->cli->backgroundGreen()->red("hello there");
    }
}
