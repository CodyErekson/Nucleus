<?php

namespace Nucleus\Helpers\Listeners;

use League\Event\AbstractListener;
use League\Event\EventInterface;

abstract class BaseListener extends AbstractListener
{
    abstract public function handle(EventInterface $event);
}
