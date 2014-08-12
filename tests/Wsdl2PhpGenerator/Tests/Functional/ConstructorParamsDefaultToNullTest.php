<?php

namespace Wsdl2PhpGenerator\Tests\Functional;

/**
 * Test handling of the ConstructorParamsDefaultToNull configuration option.
 */
class ConstructorParamsDefaultToNullTest extends Wsdl2PhpGeneratorFunctionalTestCase
{

    protected function getWsdlPath()
    {
        return $this->fixtureDir . '/extension/extension.wsdl';
    }

    protected function configureOptions()
    {
        $this->config->setConstructorParamsDefaultToNull(true);
    }

    public function testConstructorParamsDefaultToNull()
    {
        // Load the base class and check that all constructor parameters have a default null value.
        $this->assertGeneratedClassExists('BaseClass');
        $object = new \BaseClass();
        $baseClass = new \ReflectionClass($object);
        $baseClassConstructor = $baseClass->getConstructor();
        foreach ($baseClassConstructor->getParameters() as $parameter) {
            $this->assertEquals(null, $parameter->getDefaultValue(), 'Default constructor parameter value should be null.');
        }

        // Load the subclass and check that all constructor parameters also exist for the base class and that each of
        // them defaults to null.
        $this->assertGeneratedClassExists('DerivedClass1');
        $subClassObject = new \DerivedClass1();
        $subClass = new \ReflectionClass($subClassObject);
        $subClassConstructor = $subClass->getConstructor();
        foreach ($baseClassConstructor->getParameters() as $baseClassParameter) {
            $this->assertMethodHasParameter($subClassConstructor, $baseClassParameter);
        }
        foreach ($subClassConstructor->getParameters() as $parameter) {
            $this->assertEquals(null, $parameter->getDefaultValue(), 'Default constructor parameter value for subclasses should be null.');
        }
    }
}
