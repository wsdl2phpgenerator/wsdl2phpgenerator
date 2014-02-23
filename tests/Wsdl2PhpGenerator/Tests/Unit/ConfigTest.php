<?php

namespace Wsdl2PhpGenerator\Tests\Unit;

use PHPUnit_Framework_TestCase;
use Wsdl2PhpGenerator\Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    protected $options;
    protected $object;

    protected function setUp()
    {
        $this->options = array(
            'inputFile'                      => 'inputFile.xml',
            'outputDir'                      => '/tmp/output',
            'verbose'                        => false,
            'oneFile'                        => true,
            'classExists'                    => true,
            'noTypeConstructor'              => true,
            'namespaceName'                  => 'myNamespace',
            'optionsFeature'                 => array('SOAP_SINGLE_ELEMENT_ARRAYS'),
            'wsdlCache'                      => 'WSDL_CACHE_BOTH',
            'compression'                    => 'SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP',
            'classNames'                     => 'test,test2, test3',
            'prefix'                         => 'prefix',
            'suffix'                         => 'suffix',
            'sharedTypes'                    => false,
            'createAccessors'                => false,
            'constructorParamsDefaultToNull' => false,
            'noIncludes'                     => false
        );

        $this->object = new Config($this->options);
    }

    public function testGetSimpleValues()
    {
        $options = array(
            'inputFile'                      => 'inputFile.xml',
            'outputDir'                      => '/tmp/output',
            'verbose'                        => false,
            'oneFile'                        => true,
            'classExists'                    => true,
            'noTypeConstructor'              => true,
            'namespaceName'                  => 'myNamespace',
            'optionsFeature'                 => array('SOAP_SINGLE_ELEMENT_ARRAYS'),
            'wsdlCache'                      => 'WSDL_CACHE_BOTH',
            'compression'                    => 'SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP',
            'prefix'                         => 'prefix',
            'suffix'                         => 'suffix',
            'sharedTypes'                    => false,
            'createAccessors'                => false,
            'constructorParamsDefaultToNull' => false,
            'noIncludes'                     => false
        );

        foreach ($options as $key => $value) {
            $this->assertEquals($this->object->get($key), $value);
        }
    }
    /**
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
