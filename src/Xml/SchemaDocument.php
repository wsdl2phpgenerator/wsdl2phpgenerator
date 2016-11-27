<?php


namespace Wsdl2PhpGenerator\Xml;


use DOMDocument;
use DOMElement;
use Exception;
use Wsdl2PhpGenerator\ConfigInterface;
use Wsdl2PhpGenerator\StreamContextFactory;

/**
 * A SchemaDocument represents an XML element which contains type elements.
 *
 * The element may reference other schemas to generate a tree structure.
 */
class SchemaDocument extends XmlNode
{

    /**
     * The url representing the location of the schema.
     *
     * @var string
     */
    protected $url;


    /**
     * The schemas which are referenced by the current schema.
     *
     * @var SchemaDocument[]
     */
    protected $referereces;

    public function __construct(ConfigInterface $config, $xsdUrl, SchemaContext $context)
    {
        $this->url = $xsdUrl;

        // Generate a stream context used by libxml to access external resources.
        // This will allow DOMDocument to load XSDs through a proxy.
        $streamContextFactory = new StreamContextFactory();
        libxml_set_streams_context($streamContextFactory->create($config));

        $document = new DOMDocument();
        $loaded = $document->load($xsdUrl);
        if (!$loaded) {
            throw new Exception('Unable to load XML from '. $xsdUrl);
        }

        parent::__construct($document, $document->documentElement);

        $context->loaded($xsdUrl);

        // Locate and instantiate schemas which are referenced by the current schema.
        // A reference in this context can either be
        // - an import from another namespace: http://www.w3.org/TR/xmlschema-1/#composition-schemaImport
        // - an include within the same namespace: http://www.w3.org/TR/xmlschema-1/#compound-schema
        $this->referereces = array();
        foreach ($this->xpath(  '//wsdl:import/@location|' .
                                '//s:import/@schemaLocation|' .
                                '//s:include/@schemaLocation') as $reference) {
            $referenceUrl = $reference->value;
            if (strpos($referenceUrl, '//') === false) {
                $referenceUrl = dirname($xsdUrl) . '/' . $referenceUrl;
            }

            if ($context->needToLoad($referenceUrl)) {
                $this->referereces[] = new SchemaDocument($config, $referenceUrl, $context);
            }
        }
    }

    /**
     * Parses the schema for a type with a specific name.
     *
     * @param string $name The name of the type
     * @return DOMElement|null Returns the type node with the provided if it is found. Null otherwise.
     */
    public function findTypeElement($name)
    {
        $type = null;

        $elements = $this->xpath('//s:simpleType[@name=%s]|//s:complexType[@name=%s]', $name, $name);
        if ($elements->length > 0) {
            $type = $elements->item(0);
        }

        if (empty($type)) {
            foreach ($this->referereces as $import) {
                $type = $import->findTypeElement($name);
                if (!empty($type)) {
                    break;
                }
            }
        }

        return $type;
    }
}
