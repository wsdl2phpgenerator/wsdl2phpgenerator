<?php


namespace Wsdl2PhpGenerator\Tests\Functional;

/**
 * Test to ensure that references to additional files from the WSDL are resolved as expected.
 *
 * In this regard imports and includes are both treated as a reference.
 */
class ReferencesTest extends FunctionalTestCase
{

    protected $namespace = 'ReferencesTest';

    protected function getWsdlPath()
    {
        // Source: https://www.paypalobjects.com/wsdl/PayPalSvc.wsdl.
        return $this->fixtureDir . '/references/references.wsdl';
    }

    protected function configureOptions() {
        // Use a namespace to avoid name clashes.
        $this->config->set('namespaceName', $this->namespace);
    }

    public function testReferences()
    {
        // Test that all the expected classes are available.
        // Class availability it retrieved from the PHP SoapClient so these should work if the base WSDL and referenced
        // schemas are correct.
        // This also loads the classes.
        $expectedClasses = array(
            'ReferencesServiceService',
            'Author',
            'UserAuthor',
            'Book',
            'BaseClass',
            'DerivedClass',
        );
        foreach ($expectedClasses as $class) {
            $this->assertGeneratedClassExists($class, $this->namespace);
        }

        // Test that subclasses are handled correctly.
        // Extensions/base attributes for types are extracted from schemas by Wsdl2PhpGenerator custom code. This
        // should work if references are handled correctly.
        $subclasses = array(
            'UserAuthor' => 'Author',
            'DerivedClass' => 'BaseClass',
        );
        foreach ($subclasses as $subclass => $baseclass) {
            $this->assertClassSubclassOf(
                new \ReflectionClass($this->namespace . '\\' . $subclass),
                new \ReflectionClass($this->namespace . '\\' . $baseclass)
            );
        }
    }
} 
