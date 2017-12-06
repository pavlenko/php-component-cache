<?php

namespace PETest\Component\Cache;

use PE\Component\Cache\CacheItem;
use PE\Component\Cache\Exception\InvalidArgumentException;
use Psr\Cache\CacheItemInterface;

class CacheItemTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $item1 = new CacheItem('foo1', 'bar1', true);
        $item2 = new CacheItem('foo2', 'bar2', false);

        static::assertInstanceOf(CacheItemInterface::class, $item1);
        static::assertInstanceOf(CacheItemInterface::class, $item2);

        static::assertSame($item1->getKey(), 'foo1');
        static::assertSame($item2->getKey(), 'foo2');

        static::assertSame($item1->get(), 'bar1');
        static::assertSame($item2->get(), 'bar2');

        static::assertTrue($item1->isHit());
        static::assertFalse($item2->isHit());
    }

    public function testSet()
    {
        static::assertSame('baz', (new CacheItem('foo', 'bar', true))->set('baz')->get());
    }

    public function testExpiresAtDefault()
    {
        static::assertNull((new CacheItem('foo', 'bar', true))->getExpiresAt());
    }

    public function testExpiredAfterWithInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        (new CacheItem('foo', 'bar', true))->expiresAfter(false);
    }

    public function testExpiredAfterWithNull()
    {
        static::assertNull((new CacheItem('foo', 'bar', true))->expiresAfter(null)->getExpiresAt());
    }

    public function testExpiredAfterWithInt()
    {
        static::assertSame(time() + 300, (new CacheItem('foo', 'bar', true))->expiresAfter(300)->getExpiresAt());
    }

    public function testExpiredAfterWithDateInterval()
    {
        $interval = new \DateInterval('PT300S');
        static::assertSame(time() + 300, (new CacheItem('foo', 'bar', true))->expiresAfter($interval)->getExpiresAt());
    }

    public function testExpiredAtWithInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        (new CacheItem('foo', 'bar', true))->expiresAt(false);
    }

    public function testExpiredAtWithNull()
    {
        static::assertNull((new CacheItem('foo', 'bar', true))->expiresAt(null)->getExpiresAt());
    }

    public function testExpiredAtWithDateTime()
    {
        $date = new \DateTime();
        $date->add(new \DateInterval('PT300S'));

        static::assertSame(300, (new CacheItem('foo', 'bar', true))->expiresAt($date)->getExpiresAt());
    }
}
