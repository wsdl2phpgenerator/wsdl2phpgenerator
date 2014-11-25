<?php

namespace Wsdl2PhpGenerator\Tests\Unit;

use PHPUnit_Framework_TestCase;
use Wsdl2PhpGenerator\Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    protected $options;
    protected $config;

    protected function setUp()
    {
        $this->options = array(
            'inputFile'                      => 'inputFile.xml',
            'outputDir'                      => '/tmp/output',
            'verbose'                        => false,
            'namespaceName'                  => 'myNamespace',
            'classNames'                     => 'test,test2, test3',
            'sharedTypes'                    => false,
            'constructorParamsDefaultToNull' => false,
            'soapClientOptions'              => array('soap_version' => SOAP_1_1, 'trace' => true),
        );

        $this->config = new Config($this->options);
    }

    /**
     * Test non-normalized configuration fields against pre defined
     * expected values.
     *
     * This method uses the config object created while setting up this test.
     */
    public function testGetSimpleValues()
    {
        $expectedValues = array(
            'inputFile'                      => 'inputFile.xml',
            'outputDir'                      => '/tmp/output',
            'verbose'                        => false,
            'namespaceName'                  => 'myNamespace',
            'sharedTypes'                    => false,
            'constructorParamsDefaultToNull' => false,
        );

        foreach ($expectedValues as $key => $expectedValue) {
            $this->assertEquals($this->config->get($key), $expectedValue);
        }
    }

    /**
     * Test normalized configuration fields against pre defined
     * expected values.
     *
     * This method uses the config object created while setting up this test.
     */
    public function testNormalizedValues()
    {
        $expectedValues = array(
            'classNames' => array('test', 'test2', 'test3'),
            'soapClientOptions' => array(
                'soap_version' => SOAP_1_1,
                'trace' => true,
                'features' => SOAP_SINGLE_ELEMENT_ARRAYS
            ),
        );

        foreach ($expectedValues as $key => $expectedValue) {
            $this->assertEquals($this->config->get($key), $expectedValue);
        }
    }

    /**
     * Test the classNames normalizer against various form of input values.
     */
    public function testClassNamesNormalizer()
    {
        $toTest = array(
            ''                   => array(),
            'test1'              => array('test1'),
            'test1,test2'        => array('test1', 'test2'),
            'test1,test2, test3' => array('test1', 'test2', 'test3')
        );

        foreach ($toTest as $value => $expected) {
            $config = new Config(array(
                'inputFile'  => null,
                'outputDir'  => null,
                'classNames' => $value
            ));

            $this->assertEquals($config->get('classNames'), $expected);
        }
    }

    /**
     * Test that the soapClientOptions normalizer sets the SOAP_SINGLE_ELEMENT_ARRAYS by default.
     */
    public function testSoapClientOptionsNormalizer()
    {
        $defaults = array(
            'inputFile' => null,
            'outputDir' => null,
        );

        $config = new Config($defaults);
        $options = $config->get('soapClientOptions');
        $this->assertEquals(
            SOAP_SINGLE_ELEMENT_ARRAYS,
            $options['features'],
            'SOAP_SINGLE_ELEMENT_ARRAYS should be enabled by default.'
        );

        $config = new Config(array_merge($defaults, array('soapClientOptions' => array('trace' => true))));
        $options = $config->get('soapClientOptions');
        $this->assertEquals(
            SOAP_SINGLE_ELEMENT_ARRAYS,
            $options['features'],
            'SOAP_SINGLE_ELEMENT_ARRAYS should be enabled by default even if other SoapClient options are set.'
        );

        $config = new Config(
            array_merge($defaults, array('soapClientOptions' => array('features' => SOAP_SINGLE_ELEMENT_ARRAYS)))
        );
        $options = $config->get('soapClientOptions');
        $this->assertEquals(
            SOAP_SINGLE_ELEMENT_ARRAYS,
            $options['features'],
            'SOAP_SINGLE_ELEMENT_ARRAYS should be enabled if set explicitly.'
        );

        $config = new Config(
            array_merge($defaults, array('soapClientOptions' => array('features' => SOAP_USE_XSI_ARRAY_TYPE)))
        );
        $options = $config->get('soapClientOptions');
        $this->assertNotEquals(
            SOAP_SINGLE_ELEMENT_ARRAYS,
            $options['features'],
            'SOAP_SINGLE_ELEMENT_ARRAYS should not be enabled if other options have been enabled explicitly.'
        );
    }
}
