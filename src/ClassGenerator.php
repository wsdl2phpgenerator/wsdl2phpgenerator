<?php

/*
 * This file is part of the WSDL2PHPGenerator package.
 * (c) WSDL2PHPGenerator.
 */

namespace Wsdl2PhpGenerator;

use Wsdl2PhpGenerator\PhpSource\PhpClass;

/**
 * Interface for classes where instances be used can generate a PHP class.
 */
interface ClassGenerator
{
    /**
     * Returns the object represented as a PHP class.
     *
     * @return PhpClass
     */
    public function getClass();
}
