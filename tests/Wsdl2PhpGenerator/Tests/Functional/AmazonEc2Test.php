<?php
namespace Wsdl2PhpGenerator\Tests\Functional;

class AmazonEc2Test extends Wsdl2PhpGeneratorFunctionalTestCase
{

    public function setup()
    {
        // Source: https://s3.amazonaws.com/ec2-downloads/2013-10-01.ec2.wsdl.
        $this->wsdl = $this->fixtureDir . '/AmazonEc2.wsdl';
        parent::setup();
    }

    public function testNonStandardNamespace()
    {
        // Generate the code.
        $this->generator->generate($this->config);

        // Load the generated code.
        require_once $this->outputDir . '/AmazonEC2.php';

        // If we have gotten so far without errors we should be good.
        $this->assertTrue(true);
    }
}
