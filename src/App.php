<?php

namespace App\Core;

use Exception;

/**
 * This is a DI container
 * 
 */

class App
{
    /**
     * All registered keys.
     *
     * @var array
     */
    protected static $registry = [];

    /**
     * Bind a new key/value into the container.
     *
     * @param  string $key
     * @param  mixed  $value
     */
    public static function bind($key, $value)
    {
        static::$registry[$key] = $value;
    }

    /**
     * Retrieve a value from the registry.
     *
     * @param  string $key
     */
    public static function get($key)
    {
        if (!array_key_exists($key, static::$registry)) {
            throwException("No [{$key}] is bound in the App container.", new Exception());
        }

        return static::$registry[$key];
    }
}
