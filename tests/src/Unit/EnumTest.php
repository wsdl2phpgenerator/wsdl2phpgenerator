<?php


namespace Wsdl2PhpGenerator\Tests\Unit;


use Wsdl2PhpGenerator\Config;
use Wsdl2PhpGenerator\Enum;

/**
 * Unit test for the Enum class.
 */
class EnumTest extends CodeGenerationTestCase
{

    protected $namespace = 'EnumTest';

    /**
     * Test that enum values with similar names are resolved correctly.
     */
    public function testSimilarNames()
    {
        $config = new Config(
            array(
                'inputFile' => '',
                'outputDir' => '',
                'namespaceName' => $this->namespace,
            )
        );
        $enum = new Enum($config, 'Enum', 'string');

        // Some of these names contain characters which cannot appear in class constant names.
        // They will be stripped resulting in name clashes. Test to see that the valid names
        // have appended numbering avoiding these clashes.
        $valueNames = array(
            'foo' => 'foo',
            'foo!' => 'foo2',
            'foo!!' => 'foo3',
        );

        foreach (array_keys($valueNames) as $value) {
            $enum->addValue($value);
        }

        $this->generateClass($enum, $this->namespace);

        foreach (array_values($valueNames) as $name) {
            $this->assertClassHasConst($name, '\EnumTest\Enum');
        }
    }
}
