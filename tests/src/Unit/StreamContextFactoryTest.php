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

    /**
     * Test that authentication configuration is reflected in stream context.
     */
    public function testAuthorizationHeaderShouldBeDefined()
    {
        $soapOptions = array(
            'authentication' => SOAP_AUTHENTICATION_BASIC,
            'login' => 'user',
            'password' => 'secret'
        );
        $config = new Config(array(
            'inputFile' => null,
            'outputDir' => null,
            'soapClientOptions' => $soapOptions
        ));

        $factory = new StreamContextFactory();
        $resource = $factory->create($config);

        $options = stream_context_get_options($resource);

        $this->assertArrayHasKey('http', $options);

        // Authentication information should be reflected in a HTTP header.
        $this->assertArrayHasKey('header', $options['http']);
        $authHeader = 'Authorization: Basic ' . base64_encode($soapOptions['login'] . ':' . $soapOptions['password']);
        $this->assertContains($authHeader, $options['http']['header']);
    }

    /**
     * Test that authentication and proxy configuration are both reflected in stream context.
     */
    public function testAuthorizationHeaderAndProxyShouldBeDefined()
    {
        $soapOptions = array(
            'login' => 'user',
            'password' => 'secret'
        );
        $proxy = array(
            'host' => '192.168.0.1',
            'port' => 8080,
            'login' => 'proxy-user',
            'password' => 'proxy-secret',
        );
        $config = new Config(array(
            'inputFile' => null,
            'outputDir' => null,
            'soapClientOptions' => $soapOptions,
            'proxy' => $proxy
        ));

        $factory = new StreamContextFactory();
        $resource = $factory->create($config);

        $options = stream_context_get_options($resource);

        $this->assertArrayHasKey('http', $options);
        $this->assertArrayHasKey('proxy', $options['http']);
        $this->assertArrayHasKey('header', $options['http']);

        $header = 'Authorization: Basic ' . base64_encode($soapOptions['login'] . ':' . $soapOptions['password']);
        $this->assertContains($header, $options['http']['header']);

        $header = 'Proxy-Authorization: Basic ' . base64_encode($proxy['login'] . ':' . $proxy['password']);
        $this->assertContains($header, $options['http']['header']);
    }
}
