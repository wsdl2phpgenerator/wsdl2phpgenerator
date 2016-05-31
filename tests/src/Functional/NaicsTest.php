<?php
namespace Wsdl2PhpGenerator\Tests\Functional;

use SoapFault;

/**
 * Functional test where the response is a complex data structure containing
 * multiple nested objects and an array.
 *
 * The purpose here is to test that response contains the expected data
 * structure and that the actual data structure also matches the type
 * declarations in the Doc Blocks.
 */
class NaicsTest extends FunctionalTestCase
{

    protected function getWsdlPath()
    {
        // Source: http://www.webservicex.net/GenericNAICS.asmx?WSDL.
        return $this->fixtureDir . '/naics/GenericNAICS.wsdl';
    }

    /**
     * @vcr NaicsTest_testNaics
     */
    public function testNaics()
    {
        // Perform the request.
        $service = new \GenericNAICS();
        $request = new \GetNAICSByIndustry('Computer Systems');

        try {
            $response = $service->GetNAICSByIndustry($request);

            // Make sure we get a response where the actual types match expected values
            // and generated code comments.
            $this->assertTrue(get_class($response) == 'GetNAICSByIndustryResponse');
            $this->assertAttributeTypeConsistency('bool', 'GetNAICSByIndustryResult', $response);
            $this->assertAttributeInternalType('object', 'NAICSData', $response);
            $this->assertAttributeTypeConsistency('object', 'NAICSData', $response);
            $this->assertAttributeTypeConsistency('int', 'Records', $response->getNAICSData());
            $this->assertAttributeInternalType('object', 'NAICSData', $response->getNAICSData());
            $this->assertAttributeTypeConsistency('object', 'NAICSData', $response->getNAICSData());
            $this->assertAttributeTypeConsistency('array', 'NAICS', $response->getNAICSData()->getNAICSData());
            $arrayOfNaics = $response->getNAICSData()->getNAICSData();
            $naicsArray = $response->getNAICSData()->getNAICSData()->getNAICS();
            $this->checkArray($arrayOfNaics, $naicsArray);
            foreach ($naicsArray as $naics) {
                $this->assertAttributeTypeConsistency('string', 'NAICSCode', $naics);
                $this->assertAttributeTypeConsistency('string', 'Title', $naics);
                $this->assertAttributeTypeConsistency('string', 'IndustryDescription', $naics);
            }
        } catch (SoapFault $e) {
            // If an exception is thrown it should be due to a timeout. We cannot
            // guard against this when calling an external service.
            $this->assertContains('timeout', $e->getMessage());
        }

    }

    /**
     * Check ArrayAccess, Iterator and Countable implementations
     */
    protected function checkArray($arrayClass, $array)
    {
        $this->assertClassImplementsInterface($arrayClass, 'ArrayAccess');
        $this->assertClassImplementsInterface($arrayClass, 'Iterator');
        $this->assertClassImplementsInterface($arrayClass, 'Countable');

        $this->assertEquals(count($arrayClass), count($array));

        foreach ($arrayClass as $key => $value) {
            $this->assertArrayHasKey($key, $arrayClass);
            $this->assertEquals($arrayClass[$key], $array[$key]);

            $this->assertArrayHasKey($key, $array);
            $this->assertEquals($value, $array[$key]);
        }
    }

    /**
     * @vcr NaicsTest_testSingleNaics
     */
    public function testSingleNaics()
    {
        $service = new \GenericNAICS();
        // Requesting a specific ID should be a sure way to only return a single result.
        $request = new \GetNAICSByID('54151');
        $response = $service->GetNaicsByID($request);
        // Even if there is a single result there should be an ArrayOfNaics object with an array of NAICS attribute.
        // This ensures that the DocBlock is still valid. The is handled as the SOAP_SINGLE_ELEMENT_ARRAYS feature is
        // enabled by default.
        $this->assertAttributeTypeConsistency('array', 'NAICS', $response->getNAICSData()->getNAICSData());
    }
}
