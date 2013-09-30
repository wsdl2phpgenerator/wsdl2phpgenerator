<?php

/**
 * Functional test where the response is a complex data structure containing
 * multiple nested objects and an array.
 *
 * The purpose here is to test that response contains the expected data
 * structure and that the actual data structure also matches the type
 * declarations in the Doc Blocks.
 */
class NaicsTest extends Wsdl2PhpGeneratorFunctionalTestCase
{
    protected $wsdl = 'http://www.webservicex.net/GenericNAICS.asmx?WSDL';

    public function testNaics()
    {
        // Generate the code.
        $this->generator->generate($this->config);

        // Perform the request.
        require_once $this->outputDir . '/GenericNAICS.php';
        $service = new GenericNAICS();
        $request = new GetNAICSByIndustry('Computer Systems');
        $response = $service->GetNAICSByIndustry($request);

        // Make sure we get a response where the actual types match expected values
        // and generated code comments.
        $this->assertTrue(get_class($response) == 'GetNAICSByIndustryResponse');
        $this->assertAttributeTypeConsistency('bool', 'GetNAICSByIndustryResult', $response);
        $this->assertAttributeInternalType('object', 'NAICSData', $response);
        $this->assertAttributeTypeConsistency('object', 'NAICSData', $response);
        $this->assertAttributeTypeConsistency('int', 'Records', $response->NAICSData);
        $this->assertAttributeInternalType('object', 'NAICSData', $response->NAICSData);
        // $response->NAICSData->NAICSData should a NAICS but is a stdClass.
        // TODO: Fix inconsistencies between actual type and DocBlock declaration.
        // $this->assertAttributeTypeConsistency('object', 'NAICSData', $response->NAICSData);
        $this->assertAttributeTypeConsistency('array', 'NAICS', $response->NAICSData->NAICSData);
        $naicsArray = $response->NAICSData->NAICSData->NAICS;
        foreach ($naicsArray as $naics) {
            $this->assertAttributeTypeConsistency('string', 'NAICSCode', $naics);
            $this->assertAttributeTypeConsistency('string', 'Title', $naics);
            $this->assertAttributeTypeConsistency('string', 'IndustryDescription', $naics);
        }
    }

}
