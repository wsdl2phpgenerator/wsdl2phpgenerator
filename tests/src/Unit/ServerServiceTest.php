<?php


namespace Wsdl2PhpGenerator\Tests\Unit;


use Wsdl2PhpGenerator\ClassGenerator;
use Wsdl2PhpGenerator\Config;
use Wsdl2PhpGenerator\Service;
use Wsdl2PhpGenerator\ServerService;
use Wsdl2PhpGenerator\Tests\Functional\FunctionalTestCase;

/**
 * Unit test for configuration of the SoapServer.
 *
 * This is functional test case as we have to create an instance of the service class. This forces us to have a valid
 * WSDL which the server service class can be generated from.
 */
class ServerServiceTest extends CodeGenerationTestCase
{

    protected $namespace = 'SoapClientTest';

    // Use our mock soap client. It allows to retrieve the configuration it was passed.
    protected $soapclientClass = '\Wsdl2PhpGenerator\Tests\Mock\SoapClient';

    // Example options which can be passed as options to a \SoapClient instance.
    protected $soapclientOptions = array(
        'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
        'cache_wsdl' => WSDL_CACHE_NONE,
    );

    // Use our mock soap server. It allows to retrieve the configuration it was passed.
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
        $clientConfig = new Config(array(
                'inputFile' => null,
                'outputDir' => null,
                'namespaceName' => $this->namespace,
                'soapClientClass' => $this->soapclientClass,
                'soapClientOptions' => $this->soapclientOptions,
            ));

        $clientService = new Service($clientConfig, 'TestClientService', array(), 'Service description');
        $this->generateClass($clientService, $this->namespace);

        $this->assertClassExists('TestClientService', $this->namespace);

        $serverConfig = new Config(array(
                'inputFile' => null,
                'outputDir' => null,
                'namespaceName' => $this->namespace,
                'serverClassName' => 'TestAbstractServerService',
                'soapServerClass' => $this->soapserverClass,
                'soapServerOptions' => $this->soapserverOptions,
            ));

        $serverService = new ServerService($serverConfig, $clientService->getClass()->getIdentifier(), array(), 'Service description');
        $this->generateServerServiceClass($serverService, $this->namespace);

        $this->assertClassExists('TestAbstractServerService', $this->namespace);

        eval('namespace SoapClientTest; class TestServerService extends \SoapClientTest\TestAbstractServerService {}');

        $serverService = new \SoapClientTest\TestServerService();

        return $serverService;
    }

    /**
     * Generate and load a server service class into PHP memory.
     *
     * This will cause the server service class to be available for subsequent code.
     *
     * @param ClassGenerator $generator The object from which to generate the class.
     * @param string $namespace The namespace to use for the class.
     */
    private function generateServerServiceClass(ClassGenerator $generator, $namespace = null)
    {
        $source = $generator->getClass()->getSource();
        if (!empty($namespace)) {
            $source = 'namespace ' . $namespace . ';' . PHP_EOL . $source;
        }

        // We need to remove following part of code or PHP will crash!
        $source = str_replace('$this->setObject($this);', '', $source);

        // Eval the source for the generated class. This is now pretty but currently the only way we can test whether
        // the generated code is as expected. Our own code generation library does not allow us to retrieve functions
        // from the representing class.
        eval($source);
    }

    /**
     * Test configuration of custom SoapServer class.
     *
     * @depends testSoapConfig
     */
    public function testSoapServerClass($service)
    {

        // The server service class should be a subclass of the configured soap server class.
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
