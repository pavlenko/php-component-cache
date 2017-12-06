<?php

namespace PETest\Component\Cache\Driver;

use PE\Component\Cache\Driver\CompositeDriver;
use Psr\SimpleCache\CacheInterface;

class CompositeDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $driver1;

    /**
     * @var CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $driver2;

    /**
     * @var CompositeDriver
     */
    private $composite;

    protected function setUp()
    {
        $this->driver1 = $this->createMock(CacheInterface::class);
        $this->driver2 = $this->createMock(CacheInterface::class);

        $this->composite = new CompositeDriver([$this->driver1, $this->driver2]);
    }

    public function testGetFromFirstDoNotCallSecond()
    {
        $this->driver1->expects(static::once())
            ->method('get')
            ->willReturn('value');

        $this->driver2->expects(static::never())
            ->method('get')
            ->willReturn(null);

        static::assertSame('value', $this->composite->get('foo'));
    }

    public function testGetFromSecondIfFirstEmpty()
    {
        $this->driver1->expects(static::once())
            ->method('get')
            ->willReturn(null);

        $this->driver2->expects(static::once())
            ->method('get')
            ->willReturn('value');

        static::assertSame('value', $this->composite->get('foo'));
    }

    public function testGetReturnNullIfDriversEmpty()
    {
        $this->driver1->expects(static::once())
            ->method('get')
            ->willReturn(null);

        $this->driver2->expects(static::once())
            ->method('get')
            ->willReturn(null);

        static::assertNull($this->composite->get('foo'));
    }

    public function testSetCallAllDrivers()
    {
        $this->driver1->expects(static::once())
            ->method('set')
            ->willReturn(true);

        $this->driver2->expects(static::once())
            ->method('set')
            ->willReturn(true);

        static::assertTrue($this->composite->set('foo', 'bar'));
    }

    public function testSetReturnFalseIfAnyDriverReturnFalse()
    {
        $this->driver1->expects(static::once())
            ->method('set')
            ->willReturn(true);

        $this->driver2->expects(static::once())
            ->method('set')
            ->willReturn(false);

        static::assertFalse($this->composite->set('foo', 'bar'));
    }

    public function testDeleteCallAllDrivers()
    {
        $this->driver1->expects(static::once())
            ->method('delete')
            ->willReturn(true);

        $this->driver2->expects(static::once())
            ->method('delete')
            ->willReturn(true);

        static::assertTrue($this->composite->delete('foo'));
    }

    public function testDeleteReturnFalseIfAnyDriverReturnFalse()
    {
        $this->driver1->expects(static::once())
            ->method('delete')
            ->willReturn(true);

        $this->driver2->expects(static::once())
            ->method('delete')
            ->willReturn(false);

        static::assertFalse($this->composite->delete('foo'));
    }

    public function testClearCallAllDrivers()
    {
        $this->driver1->expects(static::once())
            ->method('clear')
            ->willReturn(true);

        $this->driver2->expects(static::once())
            ->method('clear')
            ->willReturn(true);

        static::assertTrue($this->composite->clear());
    }

    public function testClearReturnFalseIfAnyDriverReturnFalse()
    {
        $this->driver1->expects(static::once())
            ->method('clear')
            ->willReturn(true);

        $this->driver2->expects(static::once())
            ->method('clear')
            ->willReturn(false);

        static::assertFalse($this->composite->clear());
    }

    public function testHasReturnTrueIfFirstDriverReturnTrue()
    {
        $this->driver1->expects(static::once())
            ->method('has')
            ->willReturn(true);

        $this->driver2->expects(static::never())
            ->method('has')
            ->willReturn(false);

        static::assertTrue($this->composite->has('foo'));
    }

    public function testHasReturnTrueIfSecondDriverReturnTrue()
    {
        $this->driver1->expects(static::once())
            ->method('has')
            ->willReturn(false);

        $this->driver2->expects(static::once())
            ->method('has')
            ->willReturn(true);

        static::assertTrue($this->composite->has('foo'));
    }

    public function testHasReturnFalseIfDriversNotHas()
    {
        $this->driver1->expects(static::once())
            ->method('has')
            ->willReturn(false);

        $this->driver2->expects(static::once())
            ->method('has')
            ->willReturn(false);

        static::assertFalse($this->composite->has('foo'));
    }
}
