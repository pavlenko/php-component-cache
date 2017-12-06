<?php

namespace PE\Component\Cache\Pattern\Plugin;

use PE\Component\EventManager\EventManagerInterface;

interface PluginInterface
{
    /**
     * Attach one or more listeners
     *
     * @param EventManagerInterface $manager
     * @param int                   $priority
     */
    public function attach(EventManagerInterface $manager, $priority = 0);

    /**
     * Detach all previously attached listeners
     *
     * @param EventManagerInterface $manager
     */
    public function detach(EventManagerInterface $manager);
}