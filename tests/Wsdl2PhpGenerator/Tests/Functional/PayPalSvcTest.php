<?php
namespace Wsdl2PhpGenerator\Tests\Functional;

use SoapFault;

class PayPalSvcTest extends Wsdl2PhpGeneratorFunctionalTestCase
{

    public function setup()
    {
        // Source: https://www.paypalobjects.com/wsdl/PayPalSvc.wsdl.
        $this->wsdl = $this->fixtureDir . '/PayPalSvc.wsdl';
        parent::setup();
    }

    /**
     * Test that relative import parts are handled correctly.
     *
     * The PayPal WSDL contains imports of XSDs with relative paths. This test
     * ensures that they are imported correctly.
     */
    public function testRelativeImportPaths()
    {
        // Run the code generator.
        $this->generator->generate($this->config);

        // Ensure that classes have been generated for the main WSDL file as
        // well as the three includes.
        $this->assertGeneratedClassExists('PayPalAPIInterfaceService');
        $this->assertGeneratedClassExists('AmountType');
        $this->assertGeneratedClassExists('AccountStateCodeType');
        $this->assertGeneratedClassExists('EnhancedCheckoutDataType');
    }
}
