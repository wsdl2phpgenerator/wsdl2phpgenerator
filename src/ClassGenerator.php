<?php

namespace Wsdl2PhpGenerator;

/**
 * Interface for classes where instances be used can generate a PHP class.
 */
interface ClassGenerator
{

    /**
     * Returns the object represented as a PHP class.
     *
     * @return \Zend\Code\Generator\ClassGenerator
     */
    public function getClass();

}
