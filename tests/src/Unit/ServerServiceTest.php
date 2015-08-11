<?php


namespace Wsdl2PhpGenerator\Tests\Unit;


use Wsdl2PhpGenerator\Config;
use Wsdl2PhpGenerator\Service;
use Wsdl2PhpGenerator\Tests\Functional\FunctionalTestCase;

/**
 * Unit test for configuration of the SoapServer.
 *
 * This is functional test case as we have to create an instance of the service class. This forces us to have a valid
 * WSDL which the service class can be generated from.
 */
class ServerServiceTest extends CodeGenerationTestCase
{

    protected $namespace = 'SoapServerTest';

    // Use our mock soap server. It allows to to retrieve the configuration is was passed.
    protected $soapserverClass = '\Wsdl2PhpGenerator\Tests\Mock\SoapServer';

    // Example options which can be passed as options to a \SoapServer instance.
    protected $soapserverOptions = array(
        'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
        'cache_wsdl' => WSDL_CACHE_NONE,
    );

    /**
     * Test handling of SoapServer configuration.
     */
    public function testSoapConfig()
    {
        $config = new Config(array(
                'inputFile' => null,
                'outputDir' => null,
                'namespaceName' => $this->namespace,
                'soapServerClass' => $this->soapserverClass,
                'soapServerOptions' => $this->soapserverOptions,
            ));

        $service = new Service($config, 'TestService', array(), 'Service description');
        $this->generateClass($service, $this->namespace);

        $this->assertClassExists('TestService', $this->namespace);

        $service = new \SoapServerTest\TestService();

        return $service;
    }

    /**
     * Test configuration of custom SoapServer class.
     *
     * @depends testSoapConfig
     */
    public function testSoapServerClass($service)
    {

        // The service class should be a subclass of the configured soap server class.
        $this->assertClassSubclassOf(new \ReflectionClass($service), $this->soapserverClass);
    }

    /**
     * Test configuration of SoapServer options.
     *
     * @depends testSoapConfig
     */
    public function testSoapServerOptions($service)
    {
        // The soap server options should be the same as the ones passed to the configuration.
        $this->assertEquals($this->soapserverOptions, $service->options);
    }
}
