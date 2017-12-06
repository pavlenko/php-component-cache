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

class CacheItem implements CacheItemInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var bool
     */
    private $hit;

    /**
     * Created timestamp
     *
     * @var int|null
     */
    private $lastModified;

    /**
     * Expired timestamp
     *
     * @var int|null
     */
    private $expiresAt;

    /**
     * @param string   $key
     * @param mixed    $value
     * @param bool     $hit
     * @param int|null $lastModified
     * @param int|null $expiresAt
     */
    public function __construct($key, $value, $hit, $lastModified = null, $expiresAt = null)
    {
        $this->key   = $key;
        $this->value = $value;
        $this->hit   = $hit;

        $this->lastModified = $lastModified;
        $this->expiresAt    = $expiresAt;
    }

    /**
     * @inheritdoc
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function isHit()
    {
        return $this->hit;
    }

    /**
     * @inheritdoc
     */
    public function set($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception\InvalidArgumentException
     */
    public function expiresAt($expiration)
    {
        if (null !== $expiration && !($expiration instanceof \DateTime)) {
            throw new Exception\InvalidArgumentException('Expiration must be null or instance of DateTime');
        }

        $time = $expiration instanceof \DateTimeInterface
            ? date_diff(new \DateTime(), $expiration)
            : $expiration;

        if ($time instanceof \DateInterval) {
            $time = date_create('@0')->add($time)->getTimestamp();
        }

        $this->lastModified = time();
        $this->expiresAt   = $time;
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception\InvalidArgumentException
     */
    public function expiresAfter($time)
    {
        if (null !== $time && !is_int($time) && !($time instanceof \DateInterval)) {
            throw new Exception\InvalidArgumentException('Time must be null or int or instance of DateInterval');
        }

        $this->lastModified = time();

        if ($time instanceof \DateInterval) {
            $time = date_create('@' . $this->lastModified)->add($time)->getTimestamp();
        } else if (is_int($time)) {
            $time = $this->lastModified + $time;
        }

        $this->expiresAt = $time;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * Get cache creation time
     *
     * @return int
     */
    public function getLastModified()
    {
        return $this->lastModified ?: time();
    }
}