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

namespace PE\Component\Cache\Driver;

use Psr\SimpleCache\CacheInterface;

class CompositeDriver extends AbstractDriver
{
    /**
     * @var CacheInterface[]
     */
    protected $drivers = [];

    /**
     * @param CacheInterface[] $drivers
     */
    public function __construct(array $drivers)
    {
        foreach ($drivers as $driver ){
            $this->addDriver($driver);
        }
    }

    /**
     * @param CacheInterface $driver
     *
     * @return $this
     */
    public function addDriver(CacheInterface $driver)
    {
        $this->drivers[] = $driver;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function get($key, $default = null)
    {
        foreach ($this->drivers as $driver) {
            if (null !== ($result = $driver->get($key, $default))) {
                return $result;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = null)
    {
        $result = true;

        foreach ($this->drivers as $driver) {
            $result = $result && $driver->set($key, $value, $ttl);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        $result = true;

        foreach ($this->drivers as $driver) {
            $result = $result && $driver->delete($key);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $result = true;

        foreach ($this->drivers as $driver) {
            $result = $result && $driver->clear();
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        foreach ($this->drivers as $driver) {
            if ($driver->has($key)) {
                return true;
            }
        }

        return false;
    }
}