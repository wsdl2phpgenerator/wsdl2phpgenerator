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
            'classNames' => array('test', 'test2', 'test3')
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
        $this->assertNormalizerForParameter('classNames');
    }

    /**
     * Test the methodNames normalizer against various form of input values.
     */
    public function testMethodNamesNormalizer()
    {
        $this->assertNormalizerForParameter('methodNames');
    }

    private function assertNormalizerForParameter($parameterName)
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
                $parameterName => $value
            ));

            $this->assertEquals($config->get($parameterName), $expected);
        }
    }
}
