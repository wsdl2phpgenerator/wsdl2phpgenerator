<?php

namespace Wsdl2PhpGenerator;

interface ConfigInterface
{
    public function get($key);
    public function set($key, $value);
}
