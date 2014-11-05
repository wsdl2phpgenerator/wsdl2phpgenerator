<?php

namespace Wsdl2PhpGenerator\Tests\Functional;

/**
 * Test handling of the ConstructorParamsDefaultToNull configuration option.
 */
class ConstructorParamsDefaultToNullTest extends Wsdl2PhpGeneratorFunctionalTestCase
{

    protected $namespaceName = 'ConstructorParamsDefaultToNullTest';

    protected function getWsdlPath()
    {
        return $this->fixtureDir . '/extension/extension.wsdl';
    }

    protected function configureOptions()
    {
        $this->config->setConstructorParamsDefaultToNull(true);
        $this->config->setNamespaceName($this->namespaceName);
    }

    public function testConstructorParamsDefaultToNull()
    {
        // Load the base class and check that all constructor parameters have a default null value.
        // @todo: Remove Custom suffix for v3.
        // Other test cases contains classes with these names and thus Custom is suffixed in these situations to
        // avoid name clashes despite using a different namespace. This should be fixed in future versions.
        // Note that this also means that this test will fail when run on its own.
        $this->assertGeneratedClassExists('BaseClassCustom', $this->namespaceName);
        $object = new \ConstructorParamsDefaultToNullTest\BaseClassCustom();
        $baseClass = new \ReflectionClass($object);
        $baseClassConstructor = $baseClass->getConstructor();
        foreach ($baseClassConstructor->getParameters() as $parameter) {
            $this->assertEquals(null, $parameter->getDefaultValue(), 'Default constructor parameter value should be null.');
        }

        // Load the subclass and check that all constructor parameters also exist for the base class and that each of
        // them defaults to null.
        // @todo: More remove Custom suffix for v3.
        $this->assertGeneratedClassExists('DerivedClass1Custom', $this->namespaceName);
        $subClassObject = new \ConstructorParamsDefaultToNullTest\DerivedClass1Custom();
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
