<?php

/**
 * @package config
 */

/**
 * Interface of all config classes using key value setup
 *
 * @package config
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
interface IConfig
{
    /**
     * Binds a value to the key
     *
     * @param string $key
     * @param string $value
     */
    public function set($key, $value);

    /**
     * Returns the value bound to the key
     *
     * @param string $key
     */
    public function get($key);

    /**
     * Checks if the key is used
     *
     * @param  string $key
     * @return bool
     */
    public function exists($key);
}
