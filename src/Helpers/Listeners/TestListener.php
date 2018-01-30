<?php

namespace Nucleus\Helpers\Listeners;

use League\Event\EventInterface;

class TestListener extends BaseListener
{
    public function handle(EventInterface $event)
    {
        // Handle the event.
        echo "It worked!";
    }
}
