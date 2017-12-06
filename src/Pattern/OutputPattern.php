<?php

namespace PE\Component\Cache\Pattern;

use PE\Component\Cache\Pattern\Exception\InvalidArgumentException;
use PE\Component\Cache\Pattern\Exception\RuntimeException;

/**
 * @codeCoverageIgnore
 */
class OutputPattern extends AbstractPattern
{
    /**
     * @var string[]
     */
    private $keys = [];

    /**
     * if there is a cached item with the given key display it's data and return true
     * else start buffering output until end() is called or the script ends.
     *
     * @param string $key Key
     *
     * @return bool
     *
     * @throws InvalidArgumentException if key is missing
     */
    public function start($key)
    {
        if (($key = (string) $key) === '') {
            throw new InvalidArgumentException('Missing key to read/write output from cache');
        }

        $pool = $this->getPool();
        $item = $pool->getItem($key);

        if ($item->isHit()) {
            echo $item->get();
            return true;
        }

        ob_start();
        ob_implicit_flush(0);

        $this->keys[] = $key;

        return false;
    }

    /**
     * Stops buffering output, write buffered data to cache using the given key on start()
     * and displays the buffer.
     *
     * @throws RuntimeException if output cache not started or buffering not active
     */
    public function end()
    {
        $key = array_pop($this->keys);

        if ($key === null) {
            throw new RuntimeException('Output cache not started');
        }

        $output = ob_get_flush();

        if ($output === false) {
            throw new RuntimeException('Output buffering not active');
        }

        $pool = $this->getPool();
        $item = $pool->getItem($key);

        $item->set($output);

        $this->getPool()->save($item);
    }
}