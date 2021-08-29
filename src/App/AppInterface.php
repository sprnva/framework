<?php

namespace App\Core;

interface AppInterface
{
    /**
     * Bind a new key/value into the container.
     *
     * @param  string $key
     * @param  mixed  $value
     */
    public static function bind($key, $value);

    /**
     * Retrieve a value from the registry.
     *
     * @param  string $key
     */
    public static function get($key);
}
