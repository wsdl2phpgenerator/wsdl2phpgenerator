<?php
/**
 * @package Wsdl2PhpGenerator
 */

/**
 * Include the needed files
 */
require_once dirname(__FILE__) . '/Config.php';
require_once dirname(__FILE__) . '/Validator.php';
require_once dirname(__FILE__) . '/Variable.php';
require_once dirname(__FILE__) . '/Enum.php';
require_once dirname(__FILE__) . '/ComplexType.php';
require_once dirname(__FILE__) . '/Pattern.php';
require_once dirname(__FILE__) . '/DocumentationManager.php';
require_once dirname(__FILE__) . '/Service.php';
require_once dirname(__FILE__) . '/OutputManager.php';

// Php code classes
require_once dirname(__FILE__) . '/../lib/phpSource/PhpFile.php';

/**
 * Class that contains functionality for generating classes from a wsdl file
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Generator
{

    /**
     * Schema in simplexml format
     */
    private $schema = array();

    /**
     * A SoapClient for loading the WSDL
     * @var SoapClient
     * @access private
     */
    private $client = null;

    /**
     * DOM document used to load and parse the wsdl
     * @var DOMDocument
     * @access private
     */
    private $dom = null;

    /**
     *
     *
     * @var Service The service class
     */
    private $service;

    /**
     * An array of Type objects that represents the types in the service
     *
     * @var array Array of Type objects
     * @see Type
     */
    private $types;

    /**
     * This is the object that holds the current config
     *
     * @var Config
     * @access private
     */
    private $config;

    /**
     *
     * @var DocumentationManager A manager for the documentation
     */
    private $documentation;

    /**
     *
     * @var Generator The infamous singleton instance
     */
    private static $instance = null;

    /**
     * @var displayCallback The function called to display output internally. Initially set to gettext if set
     */
    private $displayCallback;

    /**
     * Construct the generator
     */
    private function __construct()
    {
        $this->service = null;
        $this->types = array();
        $this->enums = array();
        $this->simple = array();
        $this->documentation = new DocumentationManager();
        // default to gettext, even if its unavailable (will lead to runtime exception if not and not injected)
        $this->displayCallback = (function_exists('gettext') ? 'gettext' : null);
    }

    /**
     * Initializes the single instance if it hasn't been, and returns it if it has.
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new Generator();
        }

        return self::$instance;
    }

    /**
     * Sets the display callback to an anonymous function, or a string referring to a built-in callable
     *
     * @param callable $callback
     */
    public function setDisplayCallback($callback)
    {
        $this->displayCallback = $callback;
    }

    /**
     * Use the display callback to output a message to the logger (or otherwise).
     *
     * @param string $string
     */
    private function display($string)
    {
        $disp = $this->displayCallback;

        return $disp($string);
    }

    /**
     * Generates php source code from a wsdl file
     *
     * @see Config
     * @param Config $config The config to use for generation
     * @access public
     */
    public function generate(Config $config)
    {
        $this->config = $config;

        $this->log($this->display('Starting generation'));

        $wsdl = $this->config->getInputFile();
        if (is_array($wsdl)) {
            foreach ($wsdl as $ws) {
                $this->load($ws);
            }
        } else {
            $this->load($wsdl);
        }

        $this->savePhp();

        $this->log($this->display('Generation complete'));
    }

    /**
     * Load the wsdl file into php
     */
    private function load($wsdl)
    {
        try {
            $this->log($this->display('Loading the wsdl'));
            $this->client = new SoapClient($wsdl, array('cache_wsdl' => WSDL_CACHE_NONE));
        } catch (SoapFault $e) {
            throw new Exception('Error connecting to to the wsdl. Error: ' . $e->getMessage());
        }

        $this->log($this->display('Loading the DOM'));
        $this->dom[0] = new DOMDocument();
        $this->dom[0]->load($wsdl);

        $this->documentation->loadDocumentation($this->dom[0]);

        $sxml = simplexml_import_dom($this->dom[0]);

        foreach ($sxml->xpath('//wsdl:import/@location') as $wsdl_file) {
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
            $namespaces = $sxml->getDocNamespaces();
            if (!empty($namespaces['xsd'])) {
                foreach ($sxml->xpath('//xsd:import/@schemaLocation') as $schema_file) {
                    $schema = simplexml_load_file($schema_file);
                    $this->schema[] = $schema;
                }
            }
        }
    }

    /**
     * Loads the service class
     *
     * @access private
     */
    private function loadService()
    {
        $name = $this->dom[0]->getElementsByTagNameNS('*', 'service')->item(0)->getAttribute('name');

        $this->log($this->display('Starting to load service ') . $name);

        $this->service = new Service($name, $this->types, $this->documentation->getServiceDescription());

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

            $this->log($this->display('Loading function ') . $function);

            $this->service->addOperation($function, $params, $this->documentation->getFunctionDescription($function), $returns);
        }

        $this->log($this->display('Done loading service ') . $name);
    }

    /**
     * Loads all type classes
     *
     * @access private
     */
    private function loadTypes()
    {
        $this->log($this->display('Loading types'));

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
                $type = new ComplexType($className);
                $this->log($this->display('Loading type ') . $type->getPhpIdentifier());

                for ($i = 1; $i < $numParts - 1; $i++) {
                    $parts[$i] = trim($parts[$i]);
                    list($typename, $name) = explode(" ", substr($parts[$i], 0, strlen($parts[$i]) - 1));

                    $name = $this->cleanNamespace($name);
                    if (array_key_exists($name, $arrayVars)) {
                        $typename .= '[]';
                    }

                    $nillable = false;
                    foreach ($this->schema as $schema) {
                        $tmp = $schema->xpath('//xs:complexType[@name = "' . $className . '"]/descendant::xs:element[@name = "' . $name . '"]/@nillable');
                        if (!empty($tmp) && (string)$tmp[0] == 'true') {
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
                        $type = new Enum($className, $restriction);
                        $this->log($this->display('Loading enum ') . $type->getPhpIdentifier());
                        foreach ($enumerationList as $enum) {
                            $type->addValue($enum->attributes->getNamedItem('value')->nodeValue);
                        }
                    } elseif ($patternList->length > 0) { // If pattern
                        $type = new Pattern($className, $restriction);
                        $this->log($this->display('Loading pattern ') . $type->getPhpIdentifier());
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

        $this->log($this->display('Done loading types'));
    }

    /**
     * Find the elements with maxOccurs="unbounded"
     * @param $className
     * @return array associative array where the key is the element name and the value is the element DOM node
     * @access private
     */
    private function findArrayElements($className)
    {
        $typenode = $this->findTypenode($className);
        $arrayVars = array();
        if ($typenode) {
            $elements = $typenode->getElementsByTagName('element');

            foreach ($elements as $element) {
                $name = $element->attributes->getNamedItem('name');
                $maxOccurs = $element->attributes->getNamedItem('maxOccurs');
                if ($maxOccurs && $maxOccurs->nodeValue === 'unbounded') {
                    $arrayVars[$name->nodeValue] = $element;
                }
            }
        }

        return $arrayVars;
    }

    /**
     * Save all the loaded classes to the configured output dir
     *
     * @throws Exception If no service is loaded
     *
     * @access private
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
     * Logs a message to the standard output
     *
     * @param string $message The message to log
     */
    private function log($message)
    {
        if ($this->config->getVerbose() == true) {
            print $message . PHP_EOL;
        }
    }

    /**
     * Returns the singleton of the generator class. This may be changed to a "better" solution but I don't know any of the top of my head
     * Used by different classes to get the loaded config
     *
     * @return Generator The dreaded singleton instance
     */
    public static function getInstance()
    {
        return self::instance();
    }

    /**
     * Returns the loaded config
     *
     * @return Config The loaded config
     */
    public function getConfig()
    {
        return $this->config;
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
}
