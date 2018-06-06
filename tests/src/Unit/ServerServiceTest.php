<?php


namespace Wsdl2PhpGenerator\Tests\Unit;


use Wsdl2PhpGenerator\ClassGenerator;
use Wsdl2PhpGenerator\Config;
use Wsdl2PhpGenerator\ServerService;
use Wsdl2PhpGenerator\PhpSource\PhpClass;
use Wsdl2PhpGenerator\Tests\Functional\FunctionalTestCase;

/**
 * Unit test for configuration of the SoapServer.
 *
 * This is functional test case as we have to create an instance of the service class. This forces us to have a valid
 * WSDL which the server service class can be generated from.
 */
class ServerServiceTest extends CodeGenerationTestCase
{

    protected $namespace = 'SoapServerTest';

    // Use our mock soap server. It allows to retrieve the configuration it was passed.
    protected $soapserverClass = '\Wsdl2PhpGenerator\Tests\Mock\SoapServer';

    // Example Wsdl path.
    protected $wsdl = '/tmp/some.wsdl';

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
                'inputFile' => $this->wsdl,
                'outputDir' => null,
                'namespaceName' => $this->namespace,
                'serverClassName' => 'TestAbstractServerService',
                'soapServerClass' => $this->soapserverClass,
                'soapServerOptions' => $this->soapserverOptions,
            ));

        // First step: generate the abstract server service.
        $abstractService = new ServerService($config, '', array(), 'Service description');
        $this->generateServerServiceClass($abstractService->getClass()->getSource(), $this->namespace);

        $this->assertClassExists('TestAbstractServerService', $this->namespace);

        // Second step: generate a basic service implementation.
        $serviceClass = new PhpClass('TestServerService', false, "\\{$this->namespace}\\TestAbstractServerService");
        $this->generateServerServiceClass($serviceClass->getSource(), $this->namespace);

        $service = new \SoapServerTest\TestServerService();

        return $service;
    }

    /**
     * Load a server service class into PHP memory.
     *
     * This will cause the server service class to be available for subsequent code.
     *
     * @param string $source The class code to load.
     * @param string $namespace The namespace to use for the class.
     */
    private function generateServerServiceClass($source, $namespace = null)
    {
        if (!empty($namespace)) {
            $source = 'namespace ' . $namespace . ';' . PHP_EOL . $source;
        }

        // We need to remove following part of code or PHP will crash!
        // This happens at least with PHP >= 5.4 on Windows.
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
        $this->assertClassSubclassOf(new \ReflectionClass($service), "{$this->namespace}\\TestAbstractServerService");
        $this->assertClassSubclassOf("{$this->namespace}\\TestAbstractServerService", $this->soapserverClass);
    }

    /**
     * Test configuration of SoapServer WSDL.
     *
     * @depends testSoapConfig
     */
    public function testSoapServerWsdl($service)
    {
        // The soap server WSDL should be the same as the ones passed to the configuration.
        $this->assertEquals($this->wsdl, $service->wsdl);
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
