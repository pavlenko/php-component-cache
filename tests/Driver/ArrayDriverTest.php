<?php

namespace PETest\Component\Cache\Driver;

use PE\Component\Cache\Driver\ArrayDriver;
use PE\Component\Cache\Driver\Exception\InvalidArgumentException;

class ArrayDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ArrayDriver
     */
    private $driver;

    protected function setUp()
    {
        $this->driver = new ArrayDriver();
    }

    public function testGetWithInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->driver->get(false);
    }

    public function testSetWithInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->driver->set(false, false);
    }

    public function testDeleteWithInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->driver->delete(false);
    }

    public function testHasWithInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->driver->has(false);
    }

    public function testAPI()
    {
        static::assertNull($this->driver->get('foo'));
        static::assertFalse($this->driver->has('foo'));

        $this->driver->set('foo', 'bar');

        static::assertSame('bar', $this->driver->get('foo'));
        static::assertTrue($this->driver->has('foo'));

        $this->driver->delete('foo');

        static::assertNull($this->driver->get('foo'));
        static::assertFalse($this->driver->has('foo'));

        $this->driver->set('foo', 'bas');

        static::assertSame('bas', $this->driver->get('foo'));
        static::assertTrue($this->driver->has('foo'));

        $this->driver->clear();

        static::assertNull($this->driver->get('foo'));
        static::assertFalse($this->driver->has('foo'));
    }
}
