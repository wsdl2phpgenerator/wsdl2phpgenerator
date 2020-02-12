<?php

/*
 * This file is part of the WSDL2PHPGenerator package.
 * (c) WSDL2PHPGenerator.
 */

namespace Wsdl2PhpGenerator;

/**
 * The config interface which implemented represents
 * a configuration that is used across this project.
 */
interface ConfigInterface
{
    /**
     * Get a value from the configuration by key.
     *
     * @param $key
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Set or overwrite a configuration key with a given value.
     *
     * @param $key
     * @param $value
     *
     * @return ConfigInterface
     */
    public function set($key, $value);
}
