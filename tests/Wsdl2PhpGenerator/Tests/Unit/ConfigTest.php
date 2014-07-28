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
            'optionsFeatures'                => array('SOAP_SINGLE_ELEMENT_ARRAYS'),
            'wsdlCache'                      => 'WSDL_CACHE_BOTH',
            'compression'                    => 'SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP',
            'classNames'                     => 'test,test2, test3',
            'sharedTypes'                    => false,
            'constructorParamsDefaultToNull' => false,
            'noIncludes'                     => false
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
            'optionsFeatures'                => array('SOAP_SINGLE_ELEMENT_ARRAYS'),
            'wsdlCache'                      => 'WSDL_CACHE_BOTH',
            'compression'                    => 'SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP',
            'sharedTypes'                    => false,
            'constructorParamsDefaultToNull' => false,
            'noIncludes'                     => false
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
            'classNames' => array('test', 'test2', 'test3')
        );

        foreach ($expectedValues as $key => $expectedValue) {
            $this->assertEquals($this->config->get($key), $expectedValue);
        }
    }

    /**
     * Check if the wsdlCache field in the configuration object throws an exception
     * if one tries to fill it with a non-allowed value.
     *
     * @expectedException Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function testSetWsdlCacheToNotAllowedValue()
    {
        $config = new Config(array(
            'inputFile' => null,
            'outputDir' => null,
            'wsdlCache' => 'NOT_ALLOWED'
        ));
    }

    /**
     * Create a config object for each allowed value for the wsdlCache field in the
     * configuration object to be sure it won't throw an exception.
     */
    public function testSetWsdlCacheToAllowedValues()
    {
        $allowed = array('', 'WSDL_CACHE_NONE', 'WSDL_CACHE_DISK', 'WSDL_CACHE_MEMORY', 'WSDL_CACHE_BOTH');

        foreach ($allowed as $value) {
            $this->assertInstanceOf('Wsdl2PhpGenerator\ConfigInterface', new Config(array(
                'inputFile' => null,
                'outputDir' => null,
                'wsdlCache' => $value
            )));
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
}
