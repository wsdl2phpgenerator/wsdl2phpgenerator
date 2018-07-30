<?php

namespace Wsdl2PhpGenerator\Tests\Unit;

use RuntimeException;

if (class_exists('\PHPUnit\Framework\TestCase')) {
    /**
     * Class Wsdl2PhpGeneratorTestCase
     * PhpUnit 6+
     */
    class Wsdl2PhpGeneratorTestCase extends \PHPUnit\Framework\TestCase
    {
    }
} elseif (class_exists('\PHPUnit_Framework_TestCase')) {
    /**
     * Class Wsdl2PhpGeneratorTestCase
     * PhpUnit 5
     */
    class Wsdl2PhpGeneratorTestCase extends \PHPUnit_Framework_TestCase
    {
    }
} else {
    throw new RuntimeException('Unsupported PHPUnit version');
}
