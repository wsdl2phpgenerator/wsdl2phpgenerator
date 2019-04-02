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
    
    /**
     * Test that <Config> accepts libxmlStreamContext both as array or resource
     * @
     */
    public function testStreamContextOverrideAlternatives()
    {
        $libxmlStreamContext = array(
            'ssl' => array(
                'verify_peer'		 => false,
                'verify_peer_name'	 => false,
                'allow_self_signed'	 => true
            )
        );
        
        new Config(array(
            'inputFile' => null,
            'outputDir' => null,
            'libxmlStreamContext' => $libxmlStreamContext
        ));
        $this->addToAssertionCount(1);
        
        new Config(array(
            'inputFile' => null,
            'outputDir' => null,
            'libxmlStreamContext' => stream_context_create($libxmlStreamContext)
        ));
        $this->addToAssertionCount(1);
    }
    
    /**
     * Test that the generated stream context matches the options passed to the <Config>
     */
    public function testStreamContextOverrideResult()
    {
        $config = new Config(array(
            'inputFile' => null,
            'outputDir' => null,
            'libxmlStreamContext' => array(
                'ssl' => array(
                    'verify_peer'		 => false,
                    'verify_peer_name'	 => false,
                    'allow_self_signed'	 => true
                )
            
        )));
        
        $streamContextFactory = new StreamContextFactory();
        $resource = $streamContextFactory->create($config);

        $options = stream_context_get_options($resource);
        
        $this->assertArrayHasKey('ssl', $options);
        $this->assertArrayHasKey('verify_peer', $options['ssl']);
        $this->assertArrayHasKey('verify_peer_name', $options['ssl']);
        $this->assertArrayHasKey('allow_self_signed', $options['ssl']);
        
        $this->assertEquals($options['ssl']['verify_peer'], false);
        $this->assertEquals($options['ssl']['verify_peer_name'], false);
        $this->assertEquals($options['ssl']['allow_self_signed'], true);
    }
}
