<?php

namespace PETest\Component\Cache;

use PE\Component\Cache\ErrorHandler;

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        if (ErrorHandler::getNestedLevel()) {
            ErrorHandler::clean();
        }
    }

    public function testNestedLevel()
    {
        static::assertSame(0, ErrorHandler::getNestedLevel());

        ErrorHandler::start();
        static::assertSame(1, ErrorHandler::getNestedLevel());

        ErrorHandler::start();
        static::assertSame(2, ErrorHandler::getNestedLevel());

        ErrorHandler::stop();
        static::assertSame(1, ErrorHandler::getNestedLevel());

        ErrorHandler::stop();
        static::assertSame(0, ErrorHandler::getNestedLevel());
    }

    public function testClean()
    {
        ErrorHandler::start();
        static::assertSame(1, ErrorHandler::getNestedLevel());

        ErrorHandler::start();
        static::assertSame(2, ErrorHandler::getNestedLevel());

        ErrorHandler::clean();
        static::assertSame(0, ErrorHandler::getNestedLevel());
    }

    public function testStarted()
    {
        static::assertFalse(ErrorHandler::started());

        ErrorHandler::start();
        static::assertTrue(ErrorHandler::started());

        ErrorHandler::stop();
        static::assertFalse(ErrorHandler::started());
    }

    public function testReturnCatchedError()
    {
        ErrorHandler::start();
        strpos(); // Invalid argument list
        $err = ErrorHandler::stop();

        static::assertInstanceOf('ErrorException', $err);
    }

    public function testAddError()
    {
        ErrorHandler::start();
        ErrorHandler::addError(1, 'test-msg1', 'test-file1', 100);
        ErrorHandler::addError(2, 'test-msg2', 'test-file2', 200);

        /* @var $err \ErrorException */
        $err = ErrorHandler::stop();

        static::assertInstanceOf('ErrorException', $err);
        static::assertEquals('test-file2', $err->getFile());
        static::assertEquals('test-msg2', $err->getMessage());
        static::assertEquals(200, $err->getLine());
        static::assertEquals(0, $err->getCode());
        static::assertEquals(2, $err->getSeverity());

        /* @var $previous \ErrorException */
        $previous = $err->getPrevious();

        static::assertInstanceOf('ErrorException', $previous);
        static::assertEquals('test-file1', $previous->getFile());
        static::assertEquals('test-msg1', $previous->getMessage());
        static::assertEquals(100, $previous->getLine());
        static::assertEquals(0, $previous->getCode());
        static::assertEquals(1, $previous->getSeverity());
    }
}
