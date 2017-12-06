<?php

namespace PE\Component\Cache\Pattern;

/**
 * Capture pattern implements feature like Full Page Cache
 */
class CapturePattern extends AbstractPattern
{
    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * Capture output contents to cache
     *
     * @param string $key Unique page key
     */
    public function capture($key)
    {
        $this->getEvents()->trigger(new CapturePatternEvent(CapturePatternEvent::CAPTURE_START, $this));

        if ($this->enabled) {
            return;
        }

        ob_start(function ($buffer) use ($key) {
            $buffer = trim($buffer);

            if ('' === $buffer) {
                return $buffer;
            }

            $item = $this->getPool()->getItem($key);
            $item->set($buffer);

            $this->getEvents()->trigger(new CapturePatternEvent(CapturePatternEvent::CAPTURE_END, $this, $item));

            $this->getPool()->save($item);

            return $item->get();// <-- get contents from item because it can be modified in event
        });
    }

    /**
     * Dump cached output and exit
     *
     * @param string $key Unique page key
     */
    public function dump($key)
    {
        $this->getEvents()->trigger(new CapturePatternEvent(CapturePatternEvent::DUMP_START, $this));

        if (!$this->enabled) {
            return;
        }

        $item = $this->getPool()->getItem($key);
        if (!$item->isHit()) {
            return;
        }

        $this->getEvents()->trigger(new CapturePatternEvent(CapturePatternEvent::DUMP_END, $this, $item));

        echo $item->get();
        exit();
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;
    }
}