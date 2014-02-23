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

    public function testGetValues()
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
}
