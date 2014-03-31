<?php
/**
 * @package Wsdl2PhpGenerator
 */

namespace Wsdl2PhpGenerator;

use \Exception;
use Psr\Log\LoggerInterface;
use Wsdl2PhpGenerator\Xml\WsdlDocument;

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
     * @var WsdlDocument
     */
    private $wsdl;

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
     * Load the wsdl file into php
     */
    private function load($wsdl)
    {
        $this->log('Loading the WSDL');

        $this->wsdl = new WsdlDocument($this->config, $wsdl);

        $this->loadTypes();
        $this->loadService();
    }

    /**
     * Loads the service class
     */
    private function loadService()
    {
        $service = $this->wsdl->getService();
        $this->log('Starting to load service ' . $service->getName());

        $this->service = new Service($this->config, $service->getName(), $this->types, $service->getDocumentation());

        foreach ($this->wsdl->getOperations() as $function) {
            $this->log('Loading function ' . $function->getName());

            $this->service->addOperation($function->getName(), $function->getParams(), $function->getDocumentation(), $function->getReturns());
        }

        $this->log('Done loading service ' . $service->getName());
    }

    /**
     * Loads all type classes
     */
    private function loadTypes()
    {
        $this->log('Loading types');

        $types = $this->wsdl->getTypes();

        foreach ($types as $typeNode) {
            if ($typeNode->isArray()) {
                // skip arrays
                continue;
            }

            $type = null;

            if ($typeNode->isComplex()) {
                $type = new ComplexType($this->config, $typeNode->getName());
                $this->log('Loading type ' . $type->getPhpIdentifier());

                foreach ($typeNode->getParts() as $name => $typeName) {
                    $type->addMember($typeName, $name, $typeNode->isElementNillable($name));
                }
            } elseif ($enumValues = $typeNode->getEnumerations()) {
                $type = new Enum($this->config, $typeNode->getName(), $typeNode->getRestriction());
                array_walk($enumValues, function ($value) use ($type) {
                      $type->addValue($value);
                });
            } elseif ($pattern = $typeNode->getPattern()) {
                $type = new Pattern($this->config, $typeNode->getName(), $typeNode->getRestriction());
                $type->setValue($pattern);
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
                    $this->types[$typeNode->getName()] = $type;
                }
            }
        }

        // Loop through all types again to setup class inheritance.
        // We can only do this once all types have been loaded. Otherwise we risk referencing types which have not been
        // loaded yet.
        foreach ($types as $type) {
            if (($baseType = $type->getBase()) && isset($this->types[$baseType])) {
                $this->types[$type->getName()]->setBaseType($this->types[$baseType]);
            }
        }

        $this->log('Done loading types');
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
