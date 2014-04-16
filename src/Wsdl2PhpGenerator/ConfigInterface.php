<?php

namespace Wsdl2PhpGenerator;

/**
 * The config interface which implemented represents
 * a configuration that is used across this project.
 *
 * @package Wsdl2PhpGenerator
 * @author Jim Schmid <js@1up.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
interface ConfigInterface
{
    /**
     * Get a value from the configuration
     * by key.
     *
     * @param $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get($key);

    /**
     * Set or overwrite a configuration key with
     * a given value.
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function set($key, $value);
}
