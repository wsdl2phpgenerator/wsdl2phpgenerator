<?php

namespace Wsdl2PhpGenerator\Tests\Unit\Xml;

use Wsdl2PhpGenerator\Config;
use Wsdl2PhpGenerator\Xml\SchemaContext;
use Wsdl2PhpGenerator\Xml\SchemaDocument;

class SchemaDocumentTest extends \PHPUnit_Framework_TestCase
{
    public function testAllSchemasShouldBeLoaded()
    {
        $config = new Config([
            'inputFile' => null,
            'outputDir' => null,
        ]);
        $context = new SchemaContext();

        $schema = new SchemaDocument($config, 'tests/fixtures/wsdl/references/references.wsdl', $context);

        $this->assertFalse($context->needToLoad('tests/fixtures/wsdl/references/import.xsd'));
        $this->assertFalse($context->needToLoad('tests/fixtures/wsdl/references/include.xsd'));

        $this->assertNotNull($schema->findTypeElement('Book'), 'Type from references.wsdl');
        $this->assertNotNull($schema->findTypeElement('UserAuthor'), 'Type from import.xsd');
        $this->assertNotNull($schema->findTypeElement('BaseClass'), 'Type from include.xsd');
    }

    public function testKnownSchemaInContextShouldNotBeLoaded()
    {
        $config = new Config([
            'inputFile' => null,
            'outputDir' => null,
        ]);
        $context = new SchemaContext();
        $context->loaded('tests/fixtures/wsdl/references/include.xsd');

        $schema = new SchemaDocument($config, 'tests/fixtures/wsdl/references/references.wsdl', $context);

        $this->assertNotNull($schema->findTypeElement('Book'), 'Type from references.wsdl');
        $this->assertNotNull($schema->findTypeElement('UserAuthor'), 'Type from import.xsd');
        $this->assertNull($schema->findTypeElement('BaseClass'), 'Type from include.xsd');
    }
}
