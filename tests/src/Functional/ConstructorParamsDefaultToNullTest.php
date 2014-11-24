<?php

namespace Wsdl2PhpGenerator\Tests\Functional;

/**
 * Test handling of the ConstructorParamsDefaultToNull configuration option.
 */
class ConstructorParamsDefaultToNullTest extends FunctionalTestCase
{

    protected $namespace = 'ConstructorParamsDefaultToNullTest';

    protected function getWsdlPath()
    {
        return $this->fixtureDir . '/extension/extension.wsdl';
    }

    protected function configureOptions()
    {
        $this->config->set('namespaceName', $this->namespace);
        $this->config->set('constructorParamsDefaultToNull', true);
    }

    public function testConstructorParamsDefaultToNull()
    {
        // Load the base class and check that all constructor parameters have a default null value.
        $this->assertGeneratedClassExists('BaseClass', $this->namespace);
        $object = new \ConstructorParamsDefaultToNullTest\BaseClass();
        $baseClass = new \ReflectionClass($object);
        $baseClassConstructor = $baseClass->getConstructor();
        foreach ($baseClassConstructor->getParameters() as $parameter) {
            $this->assertEquals(null, $parameter->getDefaultValue(), 'Default constructor parameter value should be null.');
        }

        // Load the subclass and check that all constructor parameters also exist for the base class and that each of
        // them defaults to null.
        $this->assertGeneratedClassExists('DerivedClass1', $this->namespace);
        $subClassObject = new \ConstructorParamsDefaultToNullTest\DerivedClass1();
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
