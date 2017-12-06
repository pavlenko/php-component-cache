<?php

namespace PE\Component\Cache\Pattern;

use PE\Component\EventManager\Event;
use Psr\Cache\CacheItemInterface;

/**
 * @codeCoverageIgnore
 */
class CapturePatternEvent extends Event
{
    const CAPTURE_START = 'cache.capture_pattern.capture_start';
    const CAPTURE_END   = 'cache.capture_pattern.capture_end';

    const DUMP_START = 'cache.capture_pattern.dump_start';
    const DUMP_END   = 'cache.capture_pattern.dump_end';

    /**
     * @var CapturePattern
     */
    private $pattern;

    /**
     * @var CacheItemInterface
     */
    private $cacheItem;

    /**
     * @param string             $name
     * @param CapturePattern     $pattern
     * @param CacheItemInterface $cacheItem
     */
    public function __construct($name, CapturePattern $pattern, $cacheItem = null)
    {
        parent::__construct($name);

        $this->pattern   = $pattern;
        $this->cacheItem = $cacheItem;
    }

    /**
     * @return CapturePattern
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return CacheItemInterface
     */
    public function getCacheItem()
    {
        return $this->cacheItem;
    }
}