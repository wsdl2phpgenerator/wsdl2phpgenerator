<?php


namespace Wsdl2PhpGenerator\Tests\Functional;

/**
 * Test to ensure that references to additional files from the WSDL are resolved as expected.
 *
 * In this regard imports and includes are both treated as a reference.
 */
class ReferencesTest extends Wsdl2PhpGeneratorFunctionalTestCase
{

    protected $namespace = 'ReferencesTest';

    protected function getWsdlPath()
    {
        // Source: https://www.paypalobjects.com/wsdl/PayPalSvc.wsdl.
        return $this->fixtureDir . '/references/references.wsdl';
    }

    protected function configureOptions() {
        // Use a namespace to avoid name clashes.
        $this->config->setNamespaceName($this->namespace);
    }

    public function testReferences()
    {
        // Test that all the expected classes are available.
        // Class availability it retrieved from the PHP SoapClient so these should work if the base WSDL and referenced
        // schemas are correct.
        // This also loads the classes.
        // @todo: Remove Custom suffix for v3.
        // Other test cases contains classes with these names and thus Custom is suffixed in these situations to
        // avoid name clashes despite using a different namespace. This should be fixed in future versions.
        // Note that this also means that this test will fail when run on its own.
        $expectedClasses = array(
            'ReferencesServiceService',
            'AuthorCustom',
            'UserAuthorCustom',
            'BookCustom',
            'BaseClassCustom',
            'DerivedClass',
        );
        foreach ($expectedClasses as $class) {
            $this->assertGeneratedClassExists($class, $this->namespace);
        }

        // Test that subclasses are handled correctly.
        // Extensions/base attributes for types are extracted from schemas by Wsdl2PhpGenerator custom code. This
        // should work if references are handled correctly.
        // @todo: More remove Custom suffix for v3.
        $subclasses = array(
            'UserAuthorCustom' => 'AuthorCustom',
            'DerivedClass' => 'BaseClassCustom',
        );
        foreach ($subclasses as $subclass => $baseclass) {
            $this->assertClassSubclassOf(
                new \ReflectionClass($this->namespace . '\\' . $subclass),
                new \ReflectionClass($this->namespace . '\\' . $baseclass)
            );
        }
    }
} 
