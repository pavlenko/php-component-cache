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

class ArrayDriver extends AbstractDriver
{
    /**
     * @var mixed[]
     */
    private $cache = [];

    /**
     * @inheritdoc
     */
    public function get($key, $default = null)
    {
        $this->validateKey($key);
        return array_key_exists($key, $this->cache) ? $this->cache[$key] : $default;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = null)
    {
        $this->validateKey($key);
        $this->cache[$key] = $value;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        $this->validateKey($key);
        unset($this->cache[$key]);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->cache = [];
        return true;
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        $this->validateKey($key);
        return array_key_exists($key, $this->cache);
    }
}