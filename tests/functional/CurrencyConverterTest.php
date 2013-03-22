<?php

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
    protected $wsdl = 'http://www.webservicex.net/CurrencyConvertor.asmx?WSDL';

    /**
     * Perform a basic code generation/request/response scenario.
     */
    public function testCurrencyConvertor()
    {
        // Run the code generator.
        $this->generator->generate($this->config);

        // Test that we have the expected files and classes.
        $expected_classes = array(
            'CurrencyConvertor',
            'ConversionRate',
            'Currency',
            'ConversionRateResponse',
        );
        foreach ($expected_classes as $class) {
            $file = $this->outputDir . '/' . $class . '.php';
            $this->assertFileExists($file);
            require_once $file;
            $this->assertClassExists($class);
        }

        // Make sure that we have expected constants and methods.
        $this->assertClassHasConst('USD', 'Currency');
        $this->assertClassHasConst('EUR', 'Currency');
        $this->assertClassHasMethod('ConversionRate', 'CurrencyConvertor');

        // Setup and execute the service call.
        $service = new CurrencyConvertor();
        $request = new ConversionRate(Currency::USD, Currency::EUR);
        $response = $service->ConversionRate($request);

        // Test that the response is as expected.
        $this->assertTrue(get_class($response) == 'ConversionRateResponse');
        // In the end the conversion rate between USD and EUR should be a numeric.
        // It is actually a double but this type does not seem to be supported by
        // assertAttributeInternalType().
        $this->assertAttributeInternalType('numeric', 'ConversionRateResult', $response);
    }

}
