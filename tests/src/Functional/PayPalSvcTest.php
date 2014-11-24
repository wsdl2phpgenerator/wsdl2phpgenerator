<?php
namespace Wsdl2PhpGenerator\Tests\Functional;

use SoapFault;

class PayPalSvcTest extends FunctionalTestCase
{

    protected function getWsdlPath()
    {
        // Source: https://www.paypalobjects.com/wsdl/PayPalSvc.wsdl.
        return $this->fixtureDir . '/paypal/PayPalSvc.wsdl';
    }

    /**
     * Test that relative import parts are handled correctly.
     *
     * The PayPal WSDL contains imports of XSDs with relative paths. This test
     * ensures that they are imported correctly.
     */
    public function testRelativeImportPaths()
    {
        // Ensure that classes have been generated for the main WSDL file as
        // well as the three includes.
        $this->assertGeneratedClassExists('PayPalAPIInterfaceService');
        $this->assertGeneratedClassExists('AmountType');
        $this->assertGeneratedClassExists('AccountStateCodeType');
        $this->assertGeneratedClassExists('EnhancedCheckoutDataType');

        $this->assertGeneratedClassExists('RefundTransactionResponseType');
        $this->assertClassHasMethod('RefundTransactionResponseType', 'getRefundTransactionID');
        $this->assertClassHasMethod('RefundTransactionResponseType', 'setRefundTransactionID');
    }
}
