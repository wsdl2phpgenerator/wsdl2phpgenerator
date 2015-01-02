<?php


namespace Wsdl2PhpGenerator\Tests\Unit;


use Wsdl2PhpGenerator\Config;
use Wsdl2PhpGenerator\StreamContextFactory;

/**
 * Unit test for the stream context factory.
 */
class StreamContextFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test that proxy configuration is reflected in stream context.
     */
    public function testProxyStreamContext()
    {
        $proxy = array(
            'host' => '192.168.0.1',
            'port' => 8080,
            'login' => 'user',
            'password' => 'secret',
        );

        $config = new Config(array(
            'inputFile' => null,
            'outputDir' => null,
            'proxy' => $proxy,
        ));

        $streamContextFactory = new StreamContextFactory();
        $resource = $streamContextFactory->create($config);

        $options = stream_context_get_options($resource);

        $this->assertArrayHasKey('http', $options);

        // The proxy configuration should be reflected in the HTTP proxy option.
        $this->assertArrayHasKey('proxy', $options['http']);
        $this->assertEquals($proxy['host'] . ':' . $proxy['port'], $options['http']['proxy']);

        // Proxy authentication information should be reflected in a HTTP header.
        $this->assertArrayHasKey('header', $options['http']);
        $proxyAuthHeader = 'Proxy-Authorization: Basic ' . base64_encode($proxy['login'] . ':' . $proxy['password']);
        $this->assertContains($proxyAuthHeader, $options['http']['header']);
    }
}
