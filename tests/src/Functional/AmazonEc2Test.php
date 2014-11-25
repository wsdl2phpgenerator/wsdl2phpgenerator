<?php
namespace Wsdl2PhpGenerator\Tests\Functional;

class AmazonEc2Test extends FunctionalTestCase
{

    protected function getWsdlPath()
    {
        // Source: https://s3.amazonaws.com/ec2-downloads/2013-10-01.ec2.wsdl.
        return $this->fixtureDir . '/amazon/AmazonEc2.wsdl';
    }

    public function testNonStandardNamespace()
    {
        // Load a service class.
        $amazon = new \AmazonEC2();

        // If we have gotten so far without errors we should be good.
        $this->assertTrue(true);
    }
}
