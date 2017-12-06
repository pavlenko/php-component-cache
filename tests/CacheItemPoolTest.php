<?php

namespace PETest\Component\Cache;

use PE\Component\Cache\CacheItem;
use PE\Component\Cache\CacheItemPool;
use PE\Component\Cache\Exception\InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;

class CacheItemPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $driver;

    /**
     * @var CacheItemPool
     */
    private $pool;

    protected function setUp()
    {
        $this->driver = $this->createMock(CacheInterface::class);
        $this->pool   = new CacheItemPool($this->driver);
    }

    public function testGetItemInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->pool->getItem(false);
    }

    public function testGetItemDriverInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->driver->expects(static::once())
            ->method('get')
            ->willThrowException(new \PE\Component\Cache\Driver\Exception\InvalidArgumentException());

        $this->pool->getItem('foo');
    }

    public function testGetItemHit()
    {
        $this->driver->expects(static::once())
            ->method('get')
            ->willReturn(['value' => 'foo']);

        $item = $this->pool->getItem('key');

        static::assertInstanceOf(CacheItem::class, $item);
        static::assertTrue($item->isHit());
        static::assertSame('foo', $item->get());
    }

    public function testGetItemMiss()
    {
        $this->driver->expects(static::once())
            ->method('get')
            ->willReturn(null);

        $item = $this->pool->getItem('key');

        static::assertInstanceOf(CacheItem::class, $item);
        static::assertFalse($item->isHit());
        static::assertNull($item->get());
    }

    public function testGetItemsInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->pool->getItems([false]);
    }

    public function testGetItemsDriverInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->driver->expects(static::once())
            ->method('getMultiple')
            ->willThrowException(new \PE\Component\Cache\Driver\Exception\InvalidArgumentException());

        $this->pool->getItems(['foo']);
    }

    public function testGetItemsHit()
    {
        $this->driver->expects(static::once())
            ->method('getMultiple')
            ->willReturn(['key' => ['value' => 'foo']]);

        $items = $this->pool->getItems(['key']);

        static::assertInstanceOf(CacheItem::class, $items['key']);
        static::assertTrue($items['key']->isHit());
        static::assertSame('foo', $items['key']->get());
    }

    public function testGetItemsMiss()
    {
        $this->driver->expects(static::once())
            ->method('getMultiple')
            ->willReturn(['key' => null]);

        $items = $this->pool->getItems(['key']);

        static::assertInstanceOf(CacheItem::class, $items['key']);
        static::assertFalse($items['key']->isHit());
        static::assertNull($items['key']->get());
    }

    public function testHasItemHit()
    {
        $this->driver->expects(static::once())
            ->method('get')
            ->willReturn(['value' => 'foo']);

        static::assertTrue($this->pool->hasItem('key'));
    }

    public function testHasItemMiss()
    {
        $this->driver->expects(static::once())
            ->method('get')
            ->willReturn(null);

        static::assertFalse($this->pool->hasItem('key'));
    }

    public function testClearTrue()
    {
        $this->driver->expects(static::once())
            ->method('clear')
            ->willReturn(true);

        static::assertTrue($this->pool->clear());
    }

    public function testClearFalse()
    {
        $this->driver->expects(static::once())
            ->method('clear')
            ->willReturn(false);

        static::assertFalse($this->pool->clear());
    }

    public function testDeleteItemInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->pool->deleteItem(false);
    }

    public function testDeleteItemDriverInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->driver->expects(static::once())
            ->method('delete')
            ->willThrowException(new \PE\Component\Cache\Driver\Exception\InvalidArgumentException());

        $this->pool->deleteItem('foo');
    }

    public function testDeleteItemTrue()
    {
        $this->driver->expects(static::once())
            ->method('delete')
            ->willReturn(true);

        static::assertTrue($this->pool->deleteItem('foo'));
    }

    public function testDeleteItemFalse()
    {
        $this->driver->expects(static::once())
            ->method('delete')
            ->willReturn(false);

        static::assertFalse($this->pool->deleteItem('foo'));
    }

    public function testDeleteItemsInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->pool->deleteItems([false]);
    }

    public function testDeleteItemsDriverInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->driver->expects(static::once())
            ->method('deleteMultiple')
            ->willThrowException(new \PE\Component\Cache\Driver\Exception\InvalidArgumentException());

        $this->pool->deleteItems(['foo']);
    }

    public function testDeleteItemsTrue()
    {
        $this->driver->expects(static::once())
            ->method('deleteMultiple')
            ->willReturn(true);

        static::assertTrue($this->pool->deleteItems(['foo']));
    }

    public function testDeleteItemsFalse()
    {
        $this->driver->expects(static::once())
            ->method('deleteMultiple')
            ->willReturn(false);

        static::assertFalse($this->pool->deleteItems(['foo']));
    }

    public function testSaveDriverInvalidKey()
    {
        $this->driver->expects(static::once())
            ->method('set')
            ->willThrowException(new \PE\Component\Cache\Driver\Exception\InvalidArgumentException());

        static::assertFalse($this->pool->save($this->pool->getItem('foo')));
    }

    public function testSaveDeferredWithoutCommit()
    {
        $this->driver->expects(static::never())
            ->method('set')
            ->willReturn(true);

        $this->pool->saveDeferred($this->pool->getItem('foo'));
    }

    public function testSaveDeferredWithCommit()
    {
        $this->driver->expects(static::once())
            ->method('set')
            ->willReturn(true);

        $this->pool->saveDeferred($this->pool->getItem('foo'));
        $this->pool->commit();
        $this->pool->commit();
    }

    public function testSaveDeferredWithCommitButFalse()
    {
        $this->driver->expects(static::exactly(2))
            ->method('set')
            ->willReturn(false);

        $this->pool->saveDeferred($this->pool->getItem('foo'));
        $this->pool->commit();
        $this->pool->commit();
    }
}
