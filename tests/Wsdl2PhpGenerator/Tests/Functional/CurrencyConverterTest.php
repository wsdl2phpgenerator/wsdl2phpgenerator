<?php
namespace Wsdl2PhpGenerator\Tests\Functional;

use SoapFault;

/**
 * Basic functional test which ensure that we are able to generate a code
 * for a simple WSDL and use the generated code to call the service and get
 * a proper result.
 *
 * This test checks:
 * - SoapClient subclass generation
 * - Enum generation
 * - Simple request/response
 */
class CurrencyConverterTest extends Wsdl2PhpGeneratorFunctionalTestCase
{

    protected function getWsdlPath()
    {
        // Source: http://www.webservicex.net/CurrencyConvertor.asmx?WSDL.
        return $this->fixtureDir . '/currencyconvertor/CurrencyConvertor.wsdl';
    }

    /**
     * Perform a basic code generation/request/response scenario.
     *
     * @vcr CurrencyConverterTest_testCurrencyConvertor
     */
    public function testCurrencyConvertor()
    {
        // Test that we have the expected files and classes.
        $expected_classes = array(
            'CurrencyConvertor',
            'ConversionRate',
            'Currency',
            'ConversionRateResponse',
        );
        foreach ($expected_classes as $class) {
            $this->assertGeneratedClassExists($class);
        }

        // Make sure that we have expected constants and methods.
        $this->assertClassHasConst('USD', 'Currency');
        $this->assertClassHasConst('EUR', 'Currency');
        $this->assertClassHasMethod('CurrencyConvertor', 'ConversionRate');

        // Setup and execute the service call.
        $service = new \CurrencyConvertor();
        $request = new \ConversionRate(\Currency::USD, \Currency::EUR);
        try {
            $response = $service->ConversionRate($request);

            // Test that the response is as expected.
            $this->assertTrue(get_class($response) == 'ConversionRateResponse');
            // In the end the conversion rate between USD and EUR should be a numeric.
            // It is actually a double but this type does not seem to be supported by
            // assertAttributeInternalType().
            $this->assertAttributeInternalType('numeric', 'ConversionRateResult', $response);
        } catch (SoapFault $e) {
            // If an exception is thrown it should be due to a timeout. We cannot
            // guard against this when calling an external service.
            $this->assertContains('timeout', $e->getMessage());
        }

    }

}
