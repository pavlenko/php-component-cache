<?php

namespace PE\Component\Cache\Pattern;

use PE\Component\Cache\Pattern\Exception\RuntimeException;

/**
 * @codeCoverageIgnore
 */
class CallbackPattern extends AbstractPattern
{
    /**
     * @var bool
     */
    private $useCacheOutput = true;

    /**
     * @param callable $callable
     * @param array    $arguments
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function call(callable $callable, array $arguments = [])
    {
        $key  = $this->generateKey($callable, $arguments);

        $pool = $this->getPool();
        $item = $pool->getItem($key);

        if ($item->isHit()) {
            if (!is_array($value = $item->get()) || !array_key_exists(0, $value)) {
                throw new RuntimeException("Invalid cached data for key '{$key}'");
            }

            if (isset($value[1])) {
                echo $value[1];
            }

            return $value[0];
        }

        if ($this->useCacheOutput) {
            ob_start();
            ob_implicit_flush(0);
        }

        //TODO: do not cache on errors using [set|restore]_error_handler

        try {
            $return = $arguments ? call_user_func_array($callable, $arguments) : $callable();
        } catch (\Exception $ex) {
            if ($this->useCacheOutput) {
                ob_end_flush();
            }

            throw new RuntimeException($ex->getMessage(), $ex->getCode(), $ex);
        }

        $item->set($this->useCacheOutput ? [$return, ob_get_flush()] : [$return]);
        $pool->save($item);

        return $return;
    }

    /**
     * @param callable $callable
     * @param array    $arguments
     *
     * @return string
     *
     * @throws RuntimeException
     */
    private function generateKey(callable $callable, array $arguments = [])
    {
        $hash = $this->generateHash($callable, 'callable');

        if (!$arguments) {
            return $hash;
        }

        return $hash . $this->generateHash(array_values($arguments), 'arguments');
    }
}