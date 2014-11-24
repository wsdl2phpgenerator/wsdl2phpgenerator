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
     * Make an XPath query against the element with escaped variables if necessary.
     *
     * Two namespaces are preregistered with the following prefixes for ease of use:
     * - http://schemas.xmlsoap.org/wsdl/: wsdl
     * - http://www.w3.org/2001/XMLSchema: s
     *
     * @param string $query The XPath query. The query should contain placeholders for arguments sprintf-style.
     * @param mixed $args A variable number of arguments used in the query
     * @return DOMNodeList The result of the query.
     */
    protected function xpath($query, $args = null)
    {
        $xpath = new DOMXPath($this->document);
        // Preregister namespaces.
        $xpath->registerNamespace('wsdl', self::WSDL_NS);
        $xpath->registerNamespace('s', self::SCHEMA_NS);

        // Arguments containing ' and " needs escaping.
        // Inspired by https://gist.github.com/jaywilliams/2883026/#comment-813400.
        $args = func_get_args();
        array_shift($args);
        foreach ($args as &$arg) {
            if (strpos($arg, "'") === false) {
                $arg = sprintf("'%s'", $arg);
            } elseif (strpos($arg, '"') === false) {
                $arg = sprintf('"%s"', $arg);
            } else {
                $arg = sprintf("concat('%s')", str_replace("'", "',\"'\",'", $arg));
            }
        }

        // Generate XPath query with esacped arguments.
        $query = call_user_func_array('sprintf', array_merge(array($query), $args));

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
