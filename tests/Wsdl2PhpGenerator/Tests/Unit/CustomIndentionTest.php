<?php

namespace Wsdl2PhpGenerator\Tests\Unit;

use Wsdl2PhpGenerator\PhpSource\PhpClass;
use Wsdl2PhpGenerator\PhpSource\PhpFile;
use Wsdl2PhpGenerator\PhpSource\PhpVariable;

class CustomIndentionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Check that when no indention string is specified, the generated code will be indented using the default
     * indention string, which is a double-space string.
     */
    public function testStandardIndention()
    {
        $tmpDir = sys_get_temp_dir();

        // create a file with the standard indention
        $file = new PhpFile('DefaultIndention');
        $class = new PhpClass('TestClass');
        $class->addVariable(new PhpVariable('public', 'myVar'));
        $file->addClass($class);
        $file->save($tmpDir);

        // assert that the indention level equals the default one
        preg_match(
            '/^(?P<indent>[[:space:]]*)public[[:space:]]*\$myVar/m',
            file_get_contents($tmpDir . DIRECTORY_SEPARATOR . 'DefaultIndention.php'),
            $matches
        );
        $this->assertEquals(2, strlen($matches['indent']));
    }

    /**
     * Check that when a custom indention string is specified, the generated code will be indented using that string.
     */
    public function testCustomIndention()
    {
        $tmpDir = sys_get_temp_dir();

        $indentionStr = '    ';

        // create a file with the standard indention
        $file = new PhpFile('CustomIndention');
        $class = new PhpClass('TestClass', false, '', null, false, $indentionStr);
        $class->addVariable(new PhpVariable('public', 'myVar'));
        $file->addClass($class);
        $file->save($tmpDir);

        // assert that the indention level equals the given one
        preg_match(
            '/^(?P<indent>[[:space:]]*)public[[:space:]]*\$myVar/m',
            file_get_contents($tmpDir . DIRECTORY_SEPARATOR . 'CustomIndention.php'),
            $matches
        );
        $this->assertEquals(strlen($indentionStr), strlen($matches['indent']));
    }
}
