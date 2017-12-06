<?php
/**
 * SunNY Creative Technologies
 *
 *   #####                                ##     ##    ##      ##
 * ##     ##                              ###    ##    ##      ##
 * ##                                     ####   ##     ##    ##
 * ##           ##     ##    ## #####     ## ##  ##      ##  ##
 *   #####      ##     ##    ###    ##    ##  ## ##       ####
 *        ##    ##     ##    ##     ##    ##   ####        ##
 *        ##    ##     ##    ##     ##    ##    ###        ##
 * ##     ##    ##     ##    ##     ##    ##     ##        ##
 *   #####        #######    ##     ##    ##     ##        ##
 *
 * C  R  E  A  T  I  V  E     T  E  C  H  N  O  L  O  G  I  E  S
 */

namespace PE\Component\Cache\Pattern;

use PE\Component\Cache\ErrorHandler;
use PE\Component\Cache\Pattern\Exception\InvalidArgumentException;
use PE\Component\Cache\Pattern\Exception\RuntimeException;
use PE\Component\Cache\Pattern\Plugin\PluginInterface;
use PE\Component\EventManager\EventManager;
use PE\Component\EventManager\EventManagerInterface;
use Psr\Cache\CacheItemPoolInterface;

abstract class AbstractPattern
{
    /**
     * @var CacheItemPoolInterface
     */
    private $pool;

    /**
     * @var EventManagerInterface
     */
    private $events;

    /**
     * @var \SplObjectStorage
     */
    private $plugins;

    /**
     * @param CacheItemPoolInterface $pool
     * @param EventManagerInterface  $events
     */
    public function __construct(CacheItemPoolInterface $pool, EventManagerInterface $events = null)
    {
        $this->pool   = $pool;
        $this->events = $events;

        $this->plugins = new \SplObjectStorage();
    }

    /**
     * @return CacheItemPoolInterface
     */
    public function getPool()
    {
        return $this->pool;
    }

    /**
     * @return EventManagerInterface
     */
    public function getEvents()
    {
        if ($this->events === null) {
            $this->events = new EventManager();
        }

        return $this->events;
    }

    /**
     * @return \SplObjectStorage
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * @param PluginInterface $plugin
     *
     * @return bool
     */
    public function hasPlugin(PluginInterface $plugin)
    {
        return $this->plugins->contains($plugin);
    }

    /**
     * @param PluginInterface $plugin
     * @param int             $priority
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function addPlugin(PluginInterface $plugin, $priority = 0)
    {
        if ($this->plugins->contains($plugin)) {
            throw new InvalidArgumentException(sprintf(
                'Plugin of type "%s" already registered',
                get_class($plugin)
            ));
        }

        $plugin->attach($this->getEvents(), $priority);
        $this->plugins->attach($plugin);

        return $this;
    }

    /**
     * @param PluginInterface $plugin
     *
     * @return $this
     */
    public function removePlugin(PluginInterface $plugin)
    {
        if ($this->plugins->contains($plugin)) {
            $plugin->detach($this->getEvents());
            $this->plugins->detach($plugin);
        }

        return $this;
    }

    /**
     * Generate hash for passed value
     *
     * @param mixed  $value
     * @param string $name
     *
     * @return string
     *
     * @throws RuntimeException
     */
    protected function generateHash($value, $name)
    {
        ErrorHandler::start();

        try {
            $serialized = serialize($value);
        } catch (\Exception $e) {
            ErrorHandler::stop();
            throw new RuntimeException("Can't serialize {$name}: see previous exception", 0, $e);
        }

        $error = ErrorHandler::stop();

        if ($error) {
            throw new RuntimeException(
                sprintf("Cannot serialize {$name}: %s", $error->getMessage()),
                0,
                $error
            );
        }

        return md5($serialized);
    }
}