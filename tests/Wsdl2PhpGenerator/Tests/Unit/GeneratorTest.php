<?php


namespace Wsdl2PhpGenerator\Tests\Unit;


use PHPUnit_Framework_Constraint_IsAnything;
use PHPUnit_Framework_TestCase;
use Wsdl2PhpGenerator\Config;

/**
 * Unit test for the generator class.
 */
class GeneratorTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test that the Generator produces a warning if the SOAP_SINGLE_ELEMENT_ARRAYS feature is not enabled.
     */
    public function testSoapSingleElementArrays()
    {
        $mock = $this->getMockBuilder('Wsdl2PhpGenerator\Generator')
            // Add load and savePhp to stub methods to avoid attempting any WSDL analysis.
            ->setMethods(array('load', 'savePhp', 'log'))
            ->getMock();
        // The logged warning should be the second logged message - just after logging that generation has started.
        $mock->expects($this->at(1))
            ->method('log')
            ->with(new PHPUnit_Framework_Constraint_IsAnything(), 'warning');

        $config = new Config(array(
                'inputFile' => null,
                'outputDir' => null,
                'soapClientOptions' => array('features' => SOAP_USE_XSI_ARRAY_TYPE)));
        $mock->generate($config);
    }

} 
