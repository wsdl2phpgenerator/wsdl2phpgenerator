<?php


namespace Wsdl2PhpGenerator\Tests\Unit;


use Wsdl2PhpGenerator\Config;
use Wsdl2PhpGenerator\Service;
use Wsdl2PhpGenerator\Tests\Functional\FunctionalTestCase;

/**
 * Unit test for configuration of the SoapClient.
 *
 * This is functional test case as we have to create an instance of the service class. This forces us to have a valid
 * WSDL which the service class can be generated from.
 */
class ServiceTest extends CodeGenerationTestCase
{

    protected $namespace = 'SoapClientTest';

    // Use our mock soap client. It allows to to retrieve the configuration is was passed.
    protected $soapclientClass = '\Wsdl2PhpGenerator\Tests\Mock\SoapClient';

    // Example Wsdl path.
    protected $wsdl = '/tmp/some.wsdl';

    // Example options which can be passed as options to a \SoapClient instance.
    protected $soapclientOptions = array(
        'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
        'cache_wsdl' => WSDL_CACHE_NONE,
    );

    /**
     * Test handling of SoapClient configuration.
     */
    public function testSoapConfig()
    {
        $config = new Config(array(
                'inputFile' => $this->wsdl,
                'outputDir' => null,
                'namespaceName' => $this->namespace,
                'soapClientClass' => $this->soapclientClass,
                'soapClientOptions' => $this->soapclientOptions,
            ));

        $service = new Service($config, 'TestService', array(), 'Service description');
        $this->generateClass($service, $this->namespace);

        $this->assertClassExists('TestService', $this->namespace);

        return new \SoapClientTest\TestService();
    }

    /**
     * Test configuration of custom SoapClient class.
     *
     * @depends testSoapConfig
     */
    public function testSoapClientClass($service)
    {
        // The service class should be a subclass of the configured soap client class.
        $this->assertClassSubclassOf(new \ReflectionClass($service), $this->soapclientClass);
    }

    /**
     * Test configuration of SoapClient WSDL.
     *
     * @depends testSoapConfig
     */
    public function testSoapClientWsdl($service)
    {
        // The soap client WSDL should be the same as the ones passed to the configuration.
        $this->assertEquals($this->wsdl, $service->wsdl);
    }

    /**
     * Test configuration of SoapClient options.
     *
     * @depends testSoapConfig
     */
    public function testSoapClientOptions($service)
    {
        // The soap client options should be the same as the ones passed to the configuration.
        $this->assertEquals($this->soapclientOptions, $service->options);
    }
}
