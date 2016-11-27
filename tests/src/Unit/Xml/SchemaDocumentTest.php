<?php

namespace Wsdl2PhpGenerator\Tests\Unit\Xml;

use Wsdl2PhpGenerator\Xml\SchemaContext;
use Wsdl2PhpGenerator\Xml\SchemaDocument;

class SchemaDocumentTest extends \PHPUnit_Framework_TestCase
{
    public function testAllSchemasShouldBeLoaded()
    {
        $context = new SchemaContext($this->getMock('Wsdl2PhpGenerator\ConfigInterface'));

        $schema = new SchemaDocument($context, 'tests/fixtures/wsdl/references/references.wsdl');

        $this->assertFalse($context->needToLoad('tests/fixtures/wsdl/references/import.xsd'));
        $this->assertFalse($context->needToLoad('tests/fixtures/wsdl/references/include.xsd'));

        $this->assertNotNull($schema->findTypeElement('Book'), 'Type from references.wsdl');
        $this->assertNotNull($schema->findTypeElement('UserAuthor'), 'Type from import.xsd');
        $this->assertNotNull($schema->findTypeElement('BaseClass'), 'Type from include.xsd');
    }

    public function testKnownSchemaInContextShouldNotBeLoaded()
    {
        $context = new SchemaContext($this->getMock('Wsdl2PhpGenerator\ConfigInterface'));
        $context->loaded('tests/fixtures/wsdl/references/include.xsd');

        $schema = new SchemaDocument($context, 'tests/fixtures/wsdl/references/references.wsdl');

        $this->assertNotNull($schema->findTypeElement('Book'), 'Type from references.wsdl');
        $this->assertNotNull($schema->findTypeElement('UserAuthor'), 'Type from import.xsd');
        $this->assertNull($schema->findTypeElement('BaseClass'), 'Type from include.xsd');
    }
}
