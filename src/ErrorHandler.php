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

abstract class ErrorHandler
{
    /**
     * Active stack
     *
     * @var array
     */
    protected static $stack = [];

    /**
     * Check if this error handler is active
     *
     * @return bool
     */
    public static function started()
    {
        return (bool) static::getNestedLevel();
    }

    /**
     * Get the current nested level
     *
     * @return int
     */
    public static function getNestedLevel()
    {
        return count(static::$stack);
    }

    /**
     * Starting the error handler
     *
     * @param int $errorLevel
     */
    public static function start($errorLevel = \E_WARNING)
    {
        if (!static::$stack) {
            set_error_handler([static::class, 'addError'], $errorLevel);
        }

        static::$stack[] = null;
    }

    /**
     * Stopping the error handler
     *
     * @return null|\ErrorException
     */
    public static function stop()
    {
        $errorException = null;

        if (static::$stack) {
            $errorException = array_pop(static::$stack);

            if (!static::$stack) {
                restore_error_handler();
            }
        }

        return $errorException;
    }

    /**
     * Stop all active handler
     *
     * @return void
     */
    public static function clean()
    {
        if (static::$stack) {
            restore_error_handler();
        }

        static::$stack = [];
    }

    /**
     * Add an error to the stack
     *
     * @param int    $errNo
     * @param string $errStr
     * @param string $errFile
     * @param int    $errLine
     */
    public static function addError($errNo, $errStr = '', $errFile = '', $errLine = 0)
    {
        $stack = &static::$stack[count(static::$stack) - 1];
        $stack = new \ErrorException($errStr, 0, $errNo, $errFile, $errLine, $stack);
    }
}