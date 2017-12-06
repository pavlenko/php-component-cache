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

namespace PE\Component\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

//TODO item structure for get last modified and expires at values
class CacheItemPool implements CacheItemPoolInterface
{
    /**
     * @var CacheInterface
     */
    private $driver;

    /**
     * @var CacheItemInterface[]
     */
    private $deferred;

    /**
     * @param CacheInterface $driver
     */
    public function __construct(CacheInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @inheritdoc
     */
    public function getItem($key)
    {
        $this->validateKey($key);

        try {
            $value = $this->driver->get($key);

            return $this->array2item($key, (array) $value);
        } catch (InvalidArgumentException $ex) {
            throw new Exception\InvalidArgumentException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * @inheritdoc
     *
     * @return CacheItemInterface[]|iterable
     */
    public function getItems(array $keys = array())
    {
        foreach ($keys as $key) {
            $this->validateKey($key);
        }

        try {
            $result = $this->driver->getMultiple($keys);

            foreach ($result as $key => $value) {
                $result[$key] = $this->array2item($key, (array) $value);
            }

            return $result;
        } catch (InvalidArgumentException $ex) {
            throw new Exception\InvalidArgumentException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function hasItem($key)
    {
        return $this->getItem($key)->isHit();
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        return $this->driver->clear();
    }

    /**
     * @inheritdoc
     */
    public function deleteItem($key)
    {
        $this->validateKey($key);

        try {
            return $this->driver->delete($key);
        } catch (InvalidArgumentException $ex) {
            throw new Exception\InvalidArgumentException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            $this->validateKey($key);
        }

        try {
            return $this->driver->deleteMultiple($keys);
        } catch (InvalidArgumentException $ex) {
            throw new Exception\InvalidArgumentException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function save(CacheItemInterface $item)
    {
        try {
            $ttl = null;
            if ($item instanceof CacheItem) {
                $ttl   = $item->getExpiresAt() - $item->getLastModified();
                $value = $this->item2array($item);
            } else {
                $value = ['value' => $item->get()];
            }

            return $this->driver->set($item->getKey(), $value, $ttl);
        } catch (InvalidArgumentException $ex) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferred[$item->getKey()] = $item;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function commit()
    {
        $result = true;

        foreach ($this->deferred as $key => $item) {
            if ($this->save($item)) {
                unset($this->deferred[$key]);
            } else {
                $result &= false;
            }
        }

        return $result;
    }

    /**
     * @param string $key
     *
     * @throws Exception\InvalidArgumentException If any of the keys in $keys are not a legal value
     */
    private function validateKey($key)
    {
        if (!is_string($key) || $key === '') {
            throw new Exception\InvalidArgumentException(sprintf(
                'Key must be a non empty string, % given',
                is_object($key) ? get_class($key) : gettype($key)
            ));
        }
    }

    /**
     * Convert array value to cache item object
     *
     * @param string $key
     * @param array  $array
     *
     * @return CacheItem
     */
    private function array2item($key, array $array)
    {
        $value = isset($array['value'])
            ? $array['value']
            : null;

        if (null === $value) {
            return new CacheItem($key, $value, false);
        }

        $lastModified = isset($array['last_modified']) && is_int($array['last_modified'])
            ? $array['last_modified']
            : null;

        $expiresAt = isset($array['expires_at']) && is_int($array['expires_at'])
            ? $array['expires_at']
            : null;

        return new CacheItem($key, $value, true, $lastModified, $expiresAt);
    }

    /**
     * Convert cache item object to array
     *
     * @param CacheItem $item
     *
     * @return array
     */
    private function item2array(CacheItem $item)
    {
        return [
            'value'         => $item->get(),
            'last_modified' => $item->getLastModified(),
            'expires_at'    => $item->getExpiresAt(),
        ];
    }
}