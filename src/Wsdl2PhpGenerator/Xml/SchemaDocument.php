<?php


namespace Wsdl2PhpGenerator\Xml;


use DOMDocument;
use DOMElement;
use Exception;

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
     * The schemas which are imported by the current schema.
     *
     * @var SchemaDocument[]
     */
    protected $imports;

    /**
     * The urls of schemas which have already been loaded.
     *
     * We keep a record of these to avoid cyclic imports.
     *
     * @var string[]
     */
    protected static $loadedUrls;

    public function __construct($xsdUrl)
    {
        $this->url = $xsdUrl;

        $document = new DOMDocument();
        $loaded = $document->load($xsdUrl);
        if (!$loaded) {
            throw new Exception('Unable to load XML from '. $xsdUrl);
        }
        parent::__construct($document, $document->documentElement);
        // Register the schema to avoid cyclic imports.
        self::$loadedUrls[] = $xsdUrl;

        // Locate and instantiate schemas which are imported by the current schema.
        $this->imports = array();
        foreach ($this->xpath('//wsdl:import/@location|//s:import/@schemaLocation') as $import) {
            $importUrl = $import->value;
            if (strpos($importUrl, '//') === false) {
                $importUrl = dirname($xsdUrl) . '/' . $importUrl;
            }

            if (!in_array($importUrl, self::$loadedUrls)) {
                $this->imports[] = new SchemaDocument($importUrl);
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

        $elements = $this->xpath('//s:simpleType[@name="' . $name . '"]|//s:complexType[@name="' . $name . '"]');
        if ($elements->length > 0) {
            $type = $elements->item(0);
        }

        if (empty($type)) {
            foreach ($this->imports as $import) {
                $type = $import->findTypeElement($name);
                if (!empty($type)) {
                    break;
                }
            }
        }

        return $type;
    }
}
