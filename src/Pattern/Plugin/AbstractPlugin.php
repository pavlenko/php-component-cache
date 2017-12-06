<?php

namespace PE\Component\Cache\Pattern\Plugin;

use PE\Component\EventManager\EventManagerInterface;

abstract class AbstractPlugin implements PluginInterface
{
    /**
     * @inheritdoc
     */
    public function attach(EventManagerInterface $manager, $priority = 0)
    {}

    /**
     * @inheritdoc
     */
    public function detach(EventManagerInterface $manager)
    {}
}