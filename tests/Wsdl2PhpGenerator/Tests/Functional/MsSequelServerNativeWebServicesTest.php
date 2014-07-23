<?php
namespace Wsdl2PhpGenerator\Tests\Functional;

class MsSequelServerNativeWebServicesTest extends FunctionalTestCase
{

    protected function getWsdlPath()
    {
        // Source: http://msdn.microsoft.com/en-us/library/ee320274(v=sql.105).aspx.
        return $this->fixtureDir . '/mssqlns/MsSequelServerNativeWebServices.wsdl';
    }

    public function testGeneratedCode()
    {
        // Test that enums are created correctly.
        //
        // the MS-SSNWS contains enumerations with names that collide with PHP
        // keywords. This test ensures that they are created correctly.
        //
        // Load the code. This ensures that the syntax of the generated code is
        // valid.
        // We do not call the service here. It is not necessary for the check
        // at hand and we do not have a valid endpoint for the service either.
        require_once $this->outputDir . '/Sql_endpoint.php';
        $this->assertTrue(true);

        // Test that classes with datetimes are created correctly.
        //
        // This should be in a separate test method. This would however cause
        // code generated to run twice and classes would be generated twice as
        // setUp() is called twice. Since the first part of the test loads all
        // the classes the second round would add a suffix to class names to
        // avoid conflicts.
        //
        // This we test for multiple aspects in the same method. Going forward
        // we should consider separating generated code between methods - e.g.
        // by adding the test method as a namespace.
        $this->assertGeneratedClassExists('DayAsNumber');

        $object = new \DayAsNumber(new \DateTime());
        $class = new \ReflectionClass($object);

        $this->assertMethodHasParameter($class->getConstructor(), 'day', 'DateTime');
        $this->assertMethodParameterDocBlockHasType($class->getConstructor(), 'day', '\DateTime');
    }

}
