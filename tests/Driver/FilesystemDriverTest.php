<?php

namespace PETest\Component\Cache\Driver;

use PE\Component\Cache\Driver\Exception\InvalidArgumentException;
use PE\Component\Cache\Driver\FilesystemDriver;

class FilesystemDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \DateInterval
     */
    private $ttl;

    private $directory;

    protected function setUp()
    {
        $this->ttl = new \DateInterval('PT3S');

        do {
            $this->directory = sys_get_temp_dir() . '/' . uniqid('cache_', false);
        } while (file_exists($this->directory));
    }

    protected function tearDown()
    {
        if (!is_dir($this->directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->directory),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                @unlink($file->getRealPath());
            } else if ($file->isDir()) {
                @rmdir($file->getRealPath());
            }
        }

        @rmdir($this->directory);
    }

    public function testConstructWithInvalidDirectory()
    {
        $this->expectException(InvalidArgumentException::class);
        new FilesystemDriver('XZ:/foo');
    }

    public function testConstructWithInvalidTTL()
    {
        $this->expectException(InvalidArgumentException::class);
        new FilesystemDriver($this->directory, false);
    }

    public function testConstructWithInvalidUMask()
    {
        $this->expectException(InvalidArgumentException::class);
        new FilesystemDriver($this->directory, 3600, false);
    }

    public function testAPI()
    {
        $driver = new FilesystemDriver($this->directory, 5);

        static::assertNull($driver->get('foo'));
        static::assertFalse($driver->has('foo'));

        $driver->set('foo', 'bar');

        static::assertSame('bar', $driver->get('foo'));
        static::assertTrue($driver->has('foo'));

        $driver->delete('foo');

        static::assertNull($driver->get('foo'));
        static::assertFalse($driver->has('foo'));

        $driver->set('foo', 'bas');

        static::assertSame('bas', $driver->get('foo'));
        static::assertTrue($driver->has('foo'));

        $driver->clear();

        static::assertNull($driver->get('foo'));
        static::assertFalse($driver->has('foo'));
    }

    public function testPassedTTL()
    {
        $driver = new FilesystemDriver($this->directory);

        $driver->set('foo', 'bar', $this->ttl);

        static::assertSame('bar', $driver->get('foo'));
        static::assertTrue($driver->has('foo'));

        sleep(2);

        static::assertSame('bar', $driver->get('foo'));
        static::assertTrue($driver->has('foo'));

        sleep(2);

        static::assertNull($driver->get('foo'));
        static::assertFalse($driver->has('foo'));
    }

    public function testDefaultTTL()
    {
        $driver = new FilesystemDriver($this->directory, $this->ttl);

        $driver->set('foo', 'bar');

        static::assertSame('bar', $driver->get('foo'));
        static::assertTrue($driver->has('foo'));

        sleep(2);

        static::assertSame('bar', $driver->get('foo'));
        static::assertTrue($driver->has('foo'));

        sleep(2);

        static::assertNull($driver->get('foo'));
        static::assertFalse($driver->has('foo'));
    }

    public function testLongID()
    {
        $id = str_repeat('a', 150);

        $driver = new FilesystemDriver($this->directory, $this->ttl);
        $driver->set($id, 'bar');
    }
}
