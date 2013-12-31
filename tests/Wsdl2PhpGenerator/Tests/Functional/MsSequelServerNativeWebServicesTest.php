<?php
namespace Wsdl2PhpGenerator\Tests\Functional;

class MsSequelServerNativeWebServicesTest extends Wsdl2PhpGeneratorFunctionalTestCase
{

    public function setup()
    {
        // Source: http://msdn.microsoft.com/en-us/library/ee320274(v=sql.105).aspx.
        $this->wsdl = $this->fixtureDir . '/MsSequelServerNativeWebServices.wsdl';
        parent::setup();
    }

    /**
     * Test that enums are created correctly-
     *
     * the MS-SSNWS contains enumerations with names that collide with PHP
     * keywords. This test ensures that they are created correctly.
     */
    public function testEnumConstants()
    {
        // Generate the code.
        $this->generator->generate($this->config);

        // Load the code. This ensures that the syntax of the generated code is
        // valid.
        // We do not call the service here. It is not necessary for the check
        // at hand and we do not have a valid endpoint for the service either.
        require_once $this->outputDir . '/Sql_endpoint.php';
        $this->assertTrue(true);
    }
}
