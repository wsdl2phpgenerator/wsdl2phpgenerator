<?php
/**
 * @package Wsdl2PhpGenerator
 */

namespace Wsdl2PhpGenerator;

use \Exception;
use Psr\Log\LoggerInterface;
use \SoapClient;
use \SoapFault;
use \DOMDocument;
use \DOMElement;
use \SimpleXMLElement;

/**
 * Class that contains functionality for generating classes from a wsdl file
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Generator implements GeneratorInterface
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
     * @var string
     */
    const SCHEMA_NS = 'http://www.w3.org/2001/XMLSchema';

    /**
     * Schema in simplexml format
     *
     * @var \SimpleXMLElement[]
     */
    private $schema = array();

    /**
     * A SoapClient for loading the WSDL
     *
     * @var SoapClient
     */
    private $client = null;

    /**
     * DOM document used to load and parse the wsdl
     *
     * @var DOMDocument[]
     */
    private $dom = array();

    /**
     * @var Service
     */
    private $service;

    /**
     * An array of Type objects that represents the types in the service
     *
     * @var Type[]
     */
    private $types = array();

    /**
     * This is the object that holds the current config
     *
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var DocumentationManager
     */
    private $documentation;

    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * Construct the generator
     */
    public function __construct()
    {
        $this->service = null;
        $this->types = array();
        $this->enums = array();
        $this->simple = array();
        $this->documentation = new DocumentationManager();
    }

    /**
     * Generates php source code from a wsdl file
     *
     * @param ConfigInterface $config The config to use for generation
     */
    public function generate(ConfigInterface $config)
    {
        $this->config = $config;

        $this->log('Starting generation');

        $wsdl = $this->config->getInputFile();
        if (is_array($wsdl)) {
            foreach ($wsdl as $ws) {
                $this->load($ws);
            }
        } else {
            $this->load($wsdl);
        }

        $this->savePhp();

        $this->log('Generation complete', 'info');
    }


    /**
     * Find namespace prefix in schema
     *
     * @param SimpleXMLElement $schema Schema where to look for prefix
     * @param string $namespace Namespace to look for
     * @return string prefix
     */
    private static function findPrefix(SimpleXMLElement $schema, $namespace)
    {
        // Find wsdl namespace prefix
        $xnss = $schema->getDocNamespaces();
        $prefix = null;
        foreach ($xnss as $p => $xns) {
            if ($xns == $namespace) {
                $prefix = $p;
                break;
            }
        }
        if ($prefix === null) {
            throw new Exception(sprintf("No namespace found: %s", $namespace));
        } elseif ($prefix != '') {
            $prefix .= ':';
        }
        return $prefix;
    }

    /**
     * Load the wsdl file into php
     */
    private function load($wsdl)
    {
        try {
            $this->log('Loading the wsdl');
            $this->client = new SoapClient($wsdl, array('cache_wsdl' => WSDL_CACHE_NONE));
        } catch (SoapFault $e) {
            throw new Exception('Error connecting to to the wsdl. Error: ' . $e->getMessage());
        }

        $this->log('Loading the DOM');
        $this->dom[0] = new DOMDocument();
        $this->dom[0]->load($wsdl);

        $this->documentation->loadDocumentation($this->dom[0]);

        $sxml = simplexml_import_dom($this->dom[0]);

        $wsdlPrefix = self::findPrefix($sxml, self::WSDL_NS);

        foreach ($sxml->xpath("//{$wsdlPrefix}import/@location") as $wsdl_file) {
            $dom = new DOMDocument();
            $dom->load($wsdl_file);
            $this->documentation->loadDocumentation($dom);
            $this->dom[] = $dom;
        }

        $this->loadSchema();
        $this->loadTypes();
        $this->loadService();
    }

    /**
     * Load schemas
     */
    private function loadSchema()
    {
        foreach ($this->dom as $dom) {
            $sxml = simplexml_import_dom($dom);
            // Add main schema to schema array
            $this->schema[] = $sxml;
            $namespaces = $sxml->getDocNamespaces();
            if (!empty($namespaces['xsd'])) {
                foreach ($sxml->xpath('//xsd:import/@schemaLocation') as $schemaUrl) {
                    // If the URL is relative then try to do a simple
                    // conversion to an absolute one.
                    if (strpos($schemaUrl, '//') === false) {
                        $schemaUrl = dirname($this->config->getInputFile()) . '/' . $schemaUrl;
                    }
                    $this->schema[] = simplexml_load_file($schemaUrl);
                }
            }
        }
    }

    /**
     * Loads the service class
     */
    private function loadService()
    {
        $name = $this->dom[0]->getElementsByTagNameNS('*', 'service')->item(0)->getAttribute('name');

        $this->log('Starting to load service ' . $name);

        $this->service = new Service($this->config, $name, $this->types, $this->documentation->getServiceDescription());

        $functions = $this->client->__getFunctions();
        foreach ($functions as $function) {
            $matches = array();
            if (preg_match('/^(\w[\w\d_]*) (\w[\w\d_]*)\(([\w\$\d,_ ]*)\)$/', $function, $matches)) {
                $returns = $matches[1];
                $function = $matches[2];
                $params = $matches[3];
            } elseif (preg_match('/^(list\([\w\$\d,_ ]*\)) (\w[\w\d_]*)\(([\w\$\d,_ ]*)\)$/', $function, $matches)) {
                $returns = $matches[1];
                $function = $matches[2];
                $params = $matches[3];
            } else {
                // invalid function call
                throw new Exception('Invalid function call: ' . $function);
            }

            $this->log('Loading function ' . $function);

            $this->service->addOperation($function, $params, $this->documentation->getFunctionDescription($function), $returns);
        }

        $this->log('Done loading service ' . $name);
    }

    /**
     * Find already registered type by identifier
     *
     * @return Type|null Returns type with the specified identifier if it finds it. Null otherwise
     */
    private function findType($name)
    {
        foreach ($this->types as $registered_type) {
            if ($registered_type->getIdentifier() == $name) {
                return $registered_type;
            }
        }
        return null;
    }

    /**
     * Loads all type classes
     */
    private function loadTypes()
    {
        $this->log('Loading types');

        $types = $this->client->__getTypes();

        foreach ($types as $typeStr) {
            $wsdlNewline = (strpos($typeStr, "\r\n") ? "\r\n" : "\n");
            $parts = explode($wsdlNewline, $typeStr);
            $tArr = explode(" ", $parts[0]);
            $restriction = $tArr[0];
            $className = $tArr[1];

            if (substr($className, -2, 2) == '[]' || substr($className, 0, 7) == 'ArrayOf') {
                // skip arrays
                continue;
            }

            $arrayVars = $this->findArrayElements($className);
            $type = null;
            $numParts = count($parts);
            // ComplexType
            if ($numParts > 1) {
                $type = new ComplexType($this->config, $className);
                $this->log('Loading type ' . $type->getPhpIdentifier());

                foreach ($this->schema as $schema) {
                    $schemaPrefix = self::findPrefix($schema, self::SCHEMA_NS);
                    $tmp = $schema->xpath(
                        '//' . $schemaPrefix . 'complexType[@name = "' . $className . '"]/'
                        . $schemaPrefix . 'complexContent/' . $schemaPrefix . 'extension/@base'
                    );
                    if (!empty($tmp)) {
                        $baseType = $this->findType($this->cleanNamespace($tmp[0]->__toString()));
                        // Extend only complex types and if already loaded
                        // will not set if base type is defined later in wsdl
                        if ($baseType !== null && $baseType instanceof ComplexType) {
                            $type->setBaseType($baseType);
                        }
                        break;
                    }
                }

                for ($i = 1; $i < $numParts - 1; $i++) {
                    $parts[$i] = trim($parts[$i]);
                    list($typename, $name) = explode(" ", substr($parts[$i], 0, strlen($parts[$i]) - 1));

                    $name = $this->cleanNamespace($name);
                    if (array_key_exists($name, $arrayVars)) {
                        $typename .= '[]';
                    }

                    $nillable = false;
                    foreach ($this->schema as $schema) {
                        $schemaPrefix = self::findPrefix($schema, self::SCHEMA_NS);
                        $tmp = $schema->xpath(
                            '//' . $schemaPrefix . 'complexType[@name = "' . $className . '"]/'
                            . 'descendant::' . $schemaPrefix . 'element[@name = "' . $name . '"]/@nillable'
                        );
                        if (!empty($tmp) && (string) $tmp[0] == 'true') {
                            $nillable = true;
                            break;
                        }
                    }

                    $type->addMember($typename, $name, $nillable);
                }
            } else { // Enum or Pattern
                $typenode = $this->findTypenode($className);

                if ($typenode) {
                    // If enum
                    $enumerationList = $typenode->getElementsByTagName('enumeration');
                    $patternList = $typenode->getElementsByTagName('pattern');
                    if ($enumerationList->length > 0) {
                        $type = new Enum($this->config, $className, $restriction);
                        $this->log('Loading enum ' . $type->getPhpIdentifier());
                        foreach ($enumerationList as $enum) {
                            $type->addValue($enum->attributes->getNamedItem('value')->nodeValue);
                        }
                    } elseif ($patternList->length > 0) { // If pattern
                        $type = new Pattern($this->config, $className, $restriction);
                        $this->log('Loading pattern ' . $type->getPhpIdentifier());
                        $type->setValue($patternList->item(0)->attributes->getNamedItem('value')->nodeValue);
                    } else {
                        continue; // Don't load the type if we don't know what it is
                    }
                }
            }

            if ($type != null) {
                $already_registered = false;
                if ($this->config->getSharedTypes()) {
                    foreach ($this->types as $registered_types) {
                        if ($registered_types->getIdentifier() == $type->getIdentifier()) {
                            $already_registered = true;
                            break;
                        }
                    }
                }
                if (!$already_registered) {
                    $this->types[] = $type;
                }
            }
        }

        $this->log('Done loading types');
    }

    /**
     * Find the elements with maxOccurs="unbounded"
     *
     * @param $className
     * @return array Associative array where the key is the element name and the value is the element DOM node
     */
    private function findArrayElements($className)
    {
        $typenode = $this->findTypenode($className);
        $arrayVars = array();
        if ($typenode) {
            $elements = $typenode->getElementsByTagName('element');

            foreach ($elements as $element) {
                $name = $element->attributes->getNamedItem('name');
                if (!empty($name)) {
                    $maxOccurs = $element->attributes->getNamedItem('maxOccurs');
                    if ($maxOccurs && $maxOccurs->nodeValue === 'unbounded') {
                        $arrayVars[$name->nodeValue] = $element;
                    }
                } else {
                    // Whether this is actually an erroneous situation or not
                    // is not certain. It has only been observed with the
                    // PayPalSvc test and the classes seem to be generated
                    // correctly regardless of this occuring.
                    $this->log(sprintf('Unable to retrieve name attribute for element in type "%s"', $className), 'debug');
                }
            }
        }

        return $arrayVars;
    }

    /**
     * Save all the loaded classes to the configured output dir
     *
     * @throws Exception If no service is loaded
     */
    private function savePhp()
    {
        $service = $this->service->getClass();

        if ($service == null) {
            throw new Exception('No service loaded');
        }

        $output = new OutputManager($this->config);

        // Generate all type classes
        $types = array();
        foreach ($this->types as $type) {
            $class = $type->getClass();
            if ($class != null) {
                $types[] = $class;

                if (!$this->config->getOneFile() && !$this->config->getNoIncludes()) {
                    $service->addDependency($class->getIdentifier() . '.php');
                }
            }
        }

        $output->save($service, $types);
    }

    /**
     * Logs a message.
     *
     * @param string $message The message to log
     * @param string $level
     */
    private function log($message, $level = 'notice')
    {
        if (isset($this->logger)) {
            $this->logger->log($level, $message);
        }
    }

    /**
     * Takes a string and removes the xml namespace if any
     *
     * @param string $str
     * @return string The string without namespace
     */
    private function cleanNamespace($str)
    {
        if (strpos($str, ':')) {
            $arr = explode(':', $str);
            $str = $arr[1];
        }

        return $str;
    }

    /**
     * Parses the type schema for a type with the name $name
     *
     * @param string $name
     * @return DOMElement|null Returns the typenode with the name $name if it finds it. Null otherwise
     */
    private function findTypenode($name)
    {
        $typenode = null;

        foreach ($this->dom as $dom) {
            $types = $this->dom[0]->getElementsByTagName('types');
            if ($types->length > 0) {
                $schemaList = $types->item(0)->getElementsByTagName('schema');
                $schemaList = $this->dom[0]->getElementsByTagName('types')->item(0)->getElementsByTagName('schema');

                foreach ($schemaList as $schema) {
                    foreach ($schema->childNodes as $node) {
                        if ($node instanceof DOMElement) {
                            if ($node->hasAttributes()) {
                                $t = $node->attributes->getNamedItem('name');
                                if ($t) {
                                    if ($t->nodeValue == $name) {
                                        $typenode = $node;
                                    }
                                }
                            }
                        }
                    }
                }

                if ($typenode != null) {
                    return $typenode;
                }

            }
        }

        foreach ($this->schema as $schema) {
            $tmp = $schema->xpath('/xs:schema/*[@name = "' . $name . '"]');
            if (count($tmp) != 0) {
                return dom_import_simplexml($tmp[0]);
            }
        }

        return $typenode;
    }


    /*
     * Setters/getters
     */

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Returns the loaded config
     *
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }


    /*
     * Singleton logic for backwards compatibility
     * TODO: v3: remove
     */

    /**
     * @var static
     * @deprecated
     */
    protected static $instance;

    /**
     * Initializes the single instance if it hasn't been, and returns it if it has.
     * @deprecated
     */
    public static function instance()
    {
        if (static::$instance === null) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * Returns the singleton of the generator class.
     *
     * @return Generator The dreaded singleton instance
     * @deprecated
     */
    public static function getInstance()
    {
        return static::instance();
    }

}
