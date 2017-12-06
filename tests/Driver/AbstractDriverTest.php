<?php

namespace PETest\Component\Cache\Driver;

use PE\Component\Cache\Driver\AbstractDriver;
use PE\Component\Cache\Driver\Exception\InvalidArgumentException;

class AbstractDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractDriver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $driver;

    protected function setUp()
    {
        $this->driver = $this->getMockForAbstractClass(
            AbstractDriver::class,
            [],
            '',
            true,
            true,
            true,
            ['clear', 'delete', 'get', 'has', 'set']
        );
    }

    public function testGetMultipleWithInvalidKeys()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->driver->getMultiple(false);
    }

    public function testGetMultipleCallGet()
    {
        $this->driver->expects(static::exactly(2))
            ->method('get')
            ->willReturn(null);

        $this->driver->getMultiple(['foo', 'bar']);
    }

    public function testSetMultipleWithInvalidKeys()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->driver->getMultiple(false);
    }

    public function testSetMultipleCallSet()
    {
        $this->driver->expects(static::exactly(2))
            ->method('set')
            ->willReturn(true);

        $this->driver->setMultiple(['foo' => 'foo', 'bar' => 'bar']);
    }

    public function testDeleteMultipleWithInvalidKeys()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->driver->getMultiple(false);
    }

    public function testDeleteMultipleCallDelete()
    {
        $this->driver->expects(static::exactly(2))
            ->method('delete')
            ->willReturn(true);

        $this->driver->deleteMultiple(['foo', 'bar']);
    }
}
