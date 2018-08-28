<?php

namespace Wsdl2PhpGenerator\PhpSource;

use PHPUnit\Framework\TestCase;

class PhpClassTest extends TestCase
{
    /** @var PhpClass */
    private $phpClass;

    protected function setUp()
    {
        $this->phpClass = new PhpClass('Test');
    }

    public function testAddConstant()
    {
        $this->phpClass->addConstant('1', 'TEST_1');
        $this->phpClass->addConstant(true, 'TEST_2');
        $this->phpClass->addConstant(null, 'TEST_3');
        $this->assertSame(
            'class Test' . PHP_EOL
            . '{' . PHP_EOL
            . '    const TEST_1 = \'1\';' . PHP_EOL
            . '    const TEST_2 = \'1\';' . PHP_EOL
            . '    const TEST_3 = \'\';' . PHP_EOL
            . PHP_EOL
            . PHP_EOL
            . '}' . PHP_EOL,
            $this->phpClass->getSource()
        );
    }

    public function testAddConstantWithEmptyValue()
    {
        $this->phpClass->addConstant('', 'TEST');
        $this->assertSame(
            'class Test' . PHP_EOL
            . '{' . PHP_EOL
            . '    const TEST = \'\';' . PHP_EOL
            . PHP_EOL
            . PHP_EOL
            . '}' . PHP_EOL,
            $this->phpClass->getSource()
        );
    }

    public function testAddConstantThrowsExceptionIfNoNameAndValueIsSupplied()
    {
        $this->setExpectedException('Exception', 'No name supplied');
        $this->phpClass->addConstant('');
    }

    public function testAddConstantThrowsExceptionIfSameConstantIsAddedTwice()
    {
        $this->phpClass->addConstant('TEST');

        $this->setExpectedException('Exception', 'A constant of the name (TEST) does already exist.');
        $this->phpClass->addConstant('TEST');
    }
}
