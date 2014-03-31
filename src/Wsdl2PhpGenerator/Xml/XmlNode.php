<?php


namespace Wsdl2PhpGenerator\Xml;


use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;

/**
 * XmlNode represents the basic element which has been extracted from a document.
 */
abstract class XmlNode
{

    /**
     * WSDL namespace
     *
     * @var string
     */
    const WSDL_NS = 'http://schemas.xmlsoap.org/wsdl/';

    /**
     * XML Schema namespace
     *
     * @see http://www.w3.org/TR/soap12-part1/#notation
     *
     * @var string
     */
    const SCHEMA_NS = 'http://www.w3.org/2001/XMLSchema';

    /**
     * The document where the element has been extracted from.
     *
     * @var DOMDocument
     */
    protected $document;

    /**
     * The element.
     *
     * @var DOMElement
     */
    protected $element;

    /**
     * @param null $document The document which the element has been extracted from.
     * @param null $element The element.
     */
    public function __construct($document = null, $element = null)
    {
        $this->setElement($document, $element);
    }

    /**
     * Sets the document and element for the node.
     *
     * @param DOMDocument $document
     * @param DOMElement $element
     */
    public function setElement($document, $element = null)
    {
        if (empty($document)) {
            // Make sure we always have an element to query against which should not return any returns.
            $document = new DOMDocument();
            $document->loadXML('<dummy/>');
        }
        $this->document = $document;

        if (empty($element)) {
            $element = $this->document->documentElement;
        }
        $this->element = $element;
    }

    /**
     * Make an XPath query against the element.
     *
     * Two namespaces are preregistered with the following prefixes for ease of use:
     * - WSDL: wsdl
     * - Schema: s
     *
     * @param string $query The XPath query.
     * @return DOMNodeList The result of the query.
     */
    protected function xpath($query)
    {
        $xpath = new DOMXPath($this->document);
        $xpath->registerNamespace('wsdl', self::WSDL_NS);
        $xpath->registerNamespace('s', self::SCHEMA_NS);
        return $xpath->query($query, $this->element);
    }

    /**
     * Takes a string and removes the XML namespace if any.
     *
     * @param string $string A tag name.
     * @return string The tag name without namespace
     */
    protected function cleanNamespace($string)
    {
        // The part after the namespace will be the last part.
        $parts = explode(':', $string, 2);
        return end($parts);
    }
}
