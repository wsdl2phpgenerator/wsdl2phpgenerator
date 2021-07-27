<?php

/*
 * This file is part of the WSDL2PHPGenerator package.
 * (c) WSDL2PHPGenerator.
 */

namespace Wsdl2PhpGenerator\Tests\Functional;

/**
 * Test handling of UTF8 names.
 */
class Utf8Test extends FunctionalTestCase
{
    protected function getWsdlPath()
    {
        return $this->fixtureDir.'/utf8/utf8.wsdl';
    }

    public function testUtf8()
    {
        // The base service class should be available.
        $this->assertGeneratedClassExists('Utf8ServiceService');

        $serviceClass = new \ReflectionClass('Utf8ServiceService');

        $methods = array_map(function (\ReflectionMethod $method) {
            return $method->getName();
        }, $serviceClass->getMethods());

        // Validate UTF8 names on opeartions
        $this->assertContains('validarContrasena', $methods, 'Class should contain a method from an UTF8 operation name, transliterated');

        // Valid file name for UTF8 named type
        $this->assertGeneratedFileExists('MsgContrasena.php');

        // Valid class name for UTF8 named type
        $this->assertGeneratedClassExists('MsgContrasena');

        //Check arrayable UTF-8 complex type

        // Valid file name for UTF8 named type
        $this->assertGeneratedFileExists('ArrayOfMsgContrasena.php');

        // Valid class name for UTF8 named type
        $this->assertGeneratedClassExists('ArrayOfMsgContrasena');

        $arrayableComplexTypeClass = new \ReflectionClass('ArrayOfMsgContrasena');

        $arrayableComplexTypeSetterDocComment = $arrayableComplexTypeClass->getMethod('setItem')->getDocComment();

        // Validate UTF8 names in DocComment types
        $this->assertStringContainsString('@param MsgContrasena[]', $arrayableComplexTypeSetterDocComment,
            'Array setter method should contain param with valid UTF8 arrayable type, transliterated');

        $this->assertStringContainsString('@return ArrayOfMsgContrasena', $arrayableComplexTypeSetterDocComment,
            'Array setter method should contain valid UTF8 return type, transliterated');
    }
}
