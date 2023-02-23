<?php

/*
 * This file is part of the WSDL2PHPGenerator package.
 * (c) WSDL2PHPGenerator.
 */

namespace Wsdl2PhpGenerator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Wsdl2PhpGenerator\Config;

class ConfigTest extends TestCase
{
    protected $options;
    protected $config;

    protected function setUp(): void
    {
        $this->options = [
            'inputFile'                      => 'inputFile.xml',
            'outputDir'                      => '/tmp/output',
            'verbose'                        => false,
            'namespaceName'                  => 'myNamespace',
            'classNames'                     => 'test,test2, test3',
            'sharedTypes'                    => false,
            'constructorParamsDefaultToNull' => false,
            'soapClientOptions'              => ['soap_version' => SOAP_1_1, 'trace' => true],
        ];

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
        $expectedValues = [
            'inputFile'                      => 'inputFile.xml',
            'outputDir'                      => '/tmp/output',
            'verbose'                        => false,
            'namespaceName'                  => 'myNamespace',
            'sharedTypes'                    => false,
            'constructorParamsDefaultToNull' => false,
        ];

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
        $expectedValues = [
            'classNames'        => ['test', 'test2', 'test3'],
            'soapClientOptions' => [
                'soap_version' => SOAP_1_1,
                'trace'        => true,
                'features'     => SOAP_SINGLE_ELEMENT_ARRAYS,
            ],
        ];

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
     * @param string $parameterName the parameter name
     */
    private function assertNormalizerForParameter($parameterName)
    {
        $toTest = [
            ''                   => [],
            'test1'              => ['test1'],
            'test1,test2'        => ['test1', 'test2'],
            'test1,test2, test3' => ['test1', 'test2', 'test3'],
        ];

        foreach ($toTest as $value => $expected) {
            $config = new Config([
                'inputFile'    => null,
                'outputDir'    => null,
                $parameterName => $value,
            ]);

            $this->assertEquals($config->get($parameterName), $expected);
        }
    }

    /**
     * Test that the soapClientOptions normalizer sets the SOAP_SINGLE_ELEMENT_ARRAYS by default.
     */
    public function testSoapClientOptionsNormalizer()
    {
        $defaults = [
            'inputFile' => null,
            'outputDir' => null,
        ];

        $config  = new Config($defaults);
        $options = $config->get('soapClientOptions');
        $this->assertEquals(
            SOAP_SINGLE_ELEMENT_ARRAYS,
            $options['features'],
            'SOAP_SINGLE_ELEMENT_ARRAYS should be enabled by default.'
        );

        $config  = new Config(array_merge($defaults, ['soapClientOptions' => ['trace' => true]]));
        $options = $config->get('soapClientOptions');
        $this->assertEquals(
            SOAP_SINGLE_ELEMENT_ARRAYS,
            $options['features'],
            'SOAP_SINGLE_ELEMENT_ARRAYS should be enabled by default even if other SoapClient options are set.'
        );

        $config = new Config(
            array_merge($defaults, ['soapClientOptions' => ['features' => SOAP_SINGLE_ELEMENT_ARRAYS]])
        );
        $options = $config->get('soapClientOptions');
        $this->assertEquals(
            SOAP_SINGLE_ELEMENT_ARRAYS,
            $options['features'],
            'SOAP_SINGLE_ELEMENT_ARRAYS should be enabled if set explicitly.'
        );

        $config = new Config(
            array_merge($defaults, ['soapClientOptions' => ['features' => SOAP_USE_XSI_ARRAY_TYPE]])
        );
        $options = $config->get('soapClientOptions');
        $this->assertNotEquals(
            SOAP_SINGLE_ELEMENT_ARRAYS,
            $options['features'],
            'SOAP_SINGLE_ELEMENT_ARRAYS should not be enabled if other options have been enabled explicitly.'
        );
    }

    /**
     * Test the proxy normalizer.
     */
    public function testProxyNormalizer()
    {
        $toTest = [
            [
                'in'  => '192.168.0.1:8080',
                'out' => [
                    'proxy_host' => '192.168.0.1',
                    'proxy_port' => 8080,
                ],
            ],
            [
                'in'  => 'tcp://192.168.0.1:8080',
                'out' => [
                    'proxy_host' => '192.168.0.1',
                    'proxy_port' => 8080,
                ],
            ],
            [
                'in'  => 'tcp://user:secret@192.168.0.1:8080',
                'out' => [
                    'proxy_host'     => '192.168.0.1',
                    'proxy_port'     => 8080,
                    'proxy_login'    => 'user',
                    'proxy_password' => 'secret',
                ],
            ],
            [
                'in' => [
                    'host' => '192.168.0.1',
                    'port' => 8080,
                ],
                'out' => [
                    'proxy_host' => '192.168.0.1',
                    'proxy_port' => 8080,
                ],
            ],
            [
                'in' => [
                    'host'     => '192.168.0.1',
                    'port'     => 8080,
                    'login'    => 'user',
                    'password' => 'secret',
                ],
                'out' => [
                    'proxy_host'     => '192.168.0.1',
                    'proxy_port'     => 8080,
                    'proxy_login'    => 'user',
                    'proxy_password' => 'secret',
                ],
            ],
        ];

        foreach ($toTest as $testcase) {
            $config = new Config([
                'inputFile' => null,
                'outputDir' => null,
                'proxy'     => $testcase['in'],
            ]);

            $this->assertEquals($config->get('proxy'), $testcase['out']);
        }
    }

    /**
     * Test that proxy configuration is propagated to SoapClient options.
     */
    public function testSoapProxyConfiguration()
    {
        $proxyString = 'tcp://user:secret@192.168.0.1:8080';
        $proxyConfig = [
            'proxy_host'     => '192.168.0.1',
            'proxy_port'     => 8080,
            'proxy_login'    => 'user',
            'proxy_password' => 'secret',
        ];

        $config = [
            'inputFile' => null,
            'outputDir' => null,
            'proxy'     => $proxyString,
        ];
        $config = new Config($config);

        $this->assertEquals($proxyConfig, $config->get('proxy'));

        $actualConfig = $config->get('soapClientOptions');
        array_walk($proxyConfig,
            function ($expectedValue, $expectedParamName) use ($actualConfig) {
                $this->assertArrayHasKey($expectedParamName, $actualConfig);
                $this->assertSame($expectedValue, $actualConfig[$expectedParamName]);
            }
        );
    }
}
