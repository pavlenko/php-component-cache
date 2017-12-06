<?php

namespace PETest\Component\Cache\Pattern;

use PE\Component\Cache\Pattern\AbstractPattern;
use PE\Component\Cache\Pattern\Exception\InvalidArgumentException;
use PE\Component\Cache\Pattern\Exception\RuntimeException;
use PE\Component\Cache\Pattern\Plugin\PluginInterface;
use PE\Component\EventManager\EventManagerInterface;
use Psr\Cache\CacheItemPoolInterface;

class AbstractPatternTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CacheItemPoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pool;

    protected function setUp()
    {
        $this->pool = $this->createMock(CacheItemPoolInterface::class);
        //$this->pattern = new TestPattern(new CacheItemPool(new ArrayDriver()));
    }

    public function testGetPoolShouldReturnPoolPassedInConstructor()
    {
        $pattern = new TestPattern($this->pool);
        static::assertSame($this->pool, $pattern->getPool());
    }

    public function testGetEventsAlwaysShouldReturnEventManager()
    {
        $pattern = new TestPattern($this->pool);
        static::assertInstanceOf(EventManagerInterface::class, $pattern->getEvents());
    }

    public function testGetEventsShouldReturnEventManagerPassedInConstructor()
    {
        $events  = $this->createMock(EventManagerInterface::class);
        $pattern = new TestPattern($this->pool, $events);

        static::assertSame($events, $pattern->getEvents());
    }

    public function testGetPlugins()
    {
        $pattern = new TestPattern($this->pool);

        $plugins1 = $pattern->getPlugins();
        $plugins2 = $pattern->getPlugins();

        static::assertInstanceOf(\SplObjectStorage::class, $plugins1);
        static::assertInstanceOf(\SplObjectStorage::class, $plugins2);
        static::assertSame($plugins1, $plugins2);
    }

    public function testAddPluginThrowsExceptionIfPluginAddedTwice()
    {
        /* @var $plugin PluginInterface|\PHPUnit_Framework_MockObject_MockObject */
        $plugin  = $this->createMock(PluginInterface::class);
        $pattern = new TestPattern($this->pool);

        $this->expectException(InvalidArgumentException::class);

        $pattern->addPlugin($plugin)->addPlugin($plugin);
    }

    public function testAddPluginCalledPluginAttachMethod()
    {
        /* @var $plugin PluginInterface|\PHPUnit_Framework_MockObject_MockObject */
        $plugin  = $this->createMock(PluginInterface::class);
        $pattern = new TestPattern($this->pool);

        $plugin->expects(static::once())->method('attach');

        $pattern->addPlugin($plugin);
    }

    public function testHasPlugin()
    {
        /* @var $plugin PluginInterface|\PHPUnit_Framework_MockObject_MockObject */
        $plugin  = $this->createMock(PluginInterface::class);
        $pattern = new TestPattern($this->pool);

        static::assertFalse($pattern->hasPlugin($plugin));

        $pattern->addPlugin($plugin);

        static::assertTrue($pattern->hasPlugin($plugin));
    }

    public function testRemovePluginCalledPluginDetachMethod()
    {
        /* @var $plugin PluginInterface|\PHPUnit_Framework_MockObject_MockObject */
        $plugin  = $this->createMock(PluginInterface::class);
        $pattern = new TestPattern($this->pool);

        $pattern->addPlugin($plugin);

        $plugin->expects(static::once())->method('detach');

        $pattern->removePlugin($plugin);
    }

    public function testGenerateHashThrowExceptionIfSerializationErrorOccurs()
    {
        $this->expectException(RuntimeException::class);

        $pattern = new TestPattern($this->pool);
        $pattern->generateHash(new NotSerializable1(), 'foo');
    }

    public function testGenerateHashThrowExceptionIfOtherErrorOccursDuringSerialization()
    {
        $this->expectException(RuntimeException::class);

        $pattern = new TestPattern($this->pool);
        $pattern->generateHash(new NotSerializable2(), 'foo');
    }

    public function testGenerateHash()
    {
        $pattern = new TestPattern($this->pool);

        static::assertSame(md5(serialize([])), $pattern->generateHash([], 'foo'));
    }
}

class TestPattern extends AbstractPattern
{
    public function generateHash($value, $name)
    {
        return parent::generateHash($value, $name);
    }
}

class NotSerializable1
{
    public function __sleep()
    {
        throw new \Exception();
    }
}

class NotSerializable2
{
    public function __sleep()
    {
        trigger_error('test error', E_WARNING);
        return [];
    }
}
