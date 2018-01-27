<?php

namespace Nucleus\Helpers\Listeners;

use League\Event\AbstractListener;
use League\Event\EventInterface;

class TestListener extends AbstractListener
{
    public function handle(EventInterface $event)
    {
        // Handle the event.
        echo "It worked!";
    }
}