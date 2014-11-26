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
     * Test the proxy normalizer
     */
    public function testProxyNormalizer()
    {
        $toTest = array(
            array(
                'in' => '192.168.0.1:8080',
                'out' => array(
                    'proxy_host' => '192.168.0.1',
                    'proxy_port' => 8080,
                    'proxy_url' => '192.168.0.1:8080'
                )
            ),
            array(
                'in' => 'tcp://192.168.0.1:8080',
                'out' => array(
                    'proxy_host' => '192.168.0.1',
                    'proxy_port' => 8080,
                    'proxy_url' => 'tcp://192.168.0.1:8080',
                    'proxy_scheme' => 'tcp'
                )
            ),
            array(
                'in' => 'tcp://user:secret@192.168.0.1:8080',
                'out' => array(
                    'proxy_host' => '192.168.0.1',
                    'proxy_port' => 8080,
                    'proxy_url' => 'tcp://192.168.0.1:8080',
                    'proxy_scheme' => 'tcp',
                    'proxy_login' => 'user',
                    'proxy_password' => 'secret',
                    'http_header_auth' => array('Proxy-Authorization: Basic dXNlcjpzZWNyZXQ=')
                )
            ),
            array(
                'in' => array(
                    'host' => '192.168.0.1',
                    'port' => 8080
                ),
                'out' => array(
                    'proxy_host' => '192.168.0.1',
                    'proxy_port' => 8080,
                    'proxy_url' => '192.168.0.1:8080'
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
                    'proxy_url' => '192.168.0.1:8080',
                    'proxy_login' => 'user',
                    'proxy_password' => 'secret',
                    'http_header_auth' => array('Proxy-Authorization: Basic dXNlcjpzZWNyZXQ=')
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
}
