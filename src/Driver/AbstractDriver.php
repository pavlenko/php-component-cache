<?php

namespace PE\Component\Cache\Driver;

use Psr\SimpleCache\CacheInterface;

abstract class AbstractDriver implements CacheInterface
{
    /**
     * @inheritdoc
     */
    final public function getMultiple($keys, $default = null)
    {
        $this->validateCollection($keys);

        $result = [];

        // Wrapping a single call for simplify API
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    final public function setMultiple($values, $ttl = null)
    {
        $this->validateCollection($values);

        $result = true;

        // Wrapping a single call for simplify API
        foreach ($values as $key => $value) {
            $result &= $this->set($key, $value, $ttl);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    final public function deleteMultiple($keys)
    {
        $this->validateCollection($keys);

        $result = true;

        // Wrapping a single call for simplify API
        foreach ($keys as $key) {
            $result &= $this->delete($key);
        }

        return $result;
    }

    /**
     * @param string $key
     *
     * @throws Exception\InvalidArgumentException If any of the keys in $keys are not a legal value
     */
    protected function validateKey($key)
    {
        if (!is_string($key) || $key === '') {
            throw new Exception\InvalidArgumentException(sprintf(
                'Key must be a non empty string, % given',
                is_object($key) ? get_class($key) : gettype($key)
            ));
        }
    }

    /**
     * @param array|\Traversable $values
     *
     * @throws Exception\InvalidArgumentException If $value is not iterable value
     */
    protected function validateCollection($values)
    {
        if (!is_array($values) && !($values instanceof \Traversable)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Values must be an array or instance of Traversable, % given',
                is_object($values) ? get_class($values) : gettype($values)
            ));
        }
    }
}