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
        $this->assertNormalizerForParameter('classNames');
    }

    /**
     * Test the operationNames normalizer against various form of input values.
     */
    public function testOperationNamesNormalizer()
    {
        $this->assertNormalizerForParameter('operationNames');
    }

    /**
     * Assert that a parameter in either string or array form is normalized to
     * array form.
     *
     * @param string $parameterName The parameter name.
     */
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

    /**
     * Test the proxy normalizer
     */
    public function testProxyNormalizer()
    {
        $toTest = array(
            array(
                'in' => '192.168.0.1:8080',
                'out' => array(
                    'proxy_host' => '192.168.0.1',
                    'proxy_port' => 8080
                )
            ),
            array(
                'in' => 'tcp://192.168.0.1:8080',
                'out' => array(
                    'proxy_host' => '192.168.0.1',
                    'proxy_port' => 8080
                )
            ),
            array(
                'in' => 'tcp://user:secret@192.168.0.1:8080',
                'out' => array(
                    'proxy_host' => '192.168.0.1',
                    'proxy_port' => 8080,
                    'proxy_login' => 'user',
                    'proxy_password' => 'secret'
                )
            ),
            array(
                'in' => array(
                    'host' => '192.168.0.1',
                    'port' => 8080
                ),
                'out' => array(
                    'proxy_host' => '192.168.0.1',
                    'proxy_port' => 8080
                )
            ),
            array(
                'in' => array(
                    'host' => '192.168.0.1',
                    'port' => 8080,
                    'login' => 'user',
                    'password' => 'secret'
                ),
                'out' => array(
                    'proxy_host' => '192.168.0.1',
                    'proxy_port' => 8080,
                    'proxy_login' => 'user',
                    'proxy_password' => 'secret'
                )
            ),
        );

        foreach ($toTest as $testcase) {
            $config = new Config(array(
                'inputFile'  => null,
                'outputDir'  => null,
                'proxy' => $testcase['in']
            ));

            $this->assertEquals($config->get('proxy'), $testcase['out']);
        }
    }

    /**
     * Test that proxy configuration is propagated to SoapClient options.
     */
    public function testSoapProxyConfiguration()
    {
        $proxyString = 'tcp://user:secret@192.168.0.1:8080';
        $proxyConfig = array(
            'proxy_host' => '192.168.0.1',
            'proxy_port' => 8080,
            'proxy_login' => 'user',
            'proxy_password' => 'secret',
        );

        $config = array(
            'inputFile' => null,
            'outputDir' => null,
            'proxy' => $proxyString,
        );
        $config = new Config($config);

        $this->assertEquals($proxyConfig, $config->get('proxy'));
        $this->assertArraySubset($proxyConfig, $config->get('soapClientOptions'));
    }
}
