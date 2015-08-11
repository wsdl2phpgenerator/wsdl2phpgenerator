<?php

/**
 * @package Wsdl2PhpGenerator
 */
namespace Wsdl2PhpGenerator;

use Wsdl2PhpGenerator\PhpSource\PhpClass;
use Wsdl2PhpGenerator\PhpSource\PhpDocComment;
use Wsdl2PhpGenerator\PhpSource\PhpDocElementFactory;
use Wsdl2PhpGenerator\PhpSource\PhpFunction;
use Wsdl2PhpGenerator\PhpSource\PhpVariable;

/**
 * Service represents the service in the wsdl, from the server side
 *
 * @package Wsdl2PhpGenerator
 * @author Thierry Blind <thierryblind@msn.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class ServerService extends Service
{
    const SERVER_SERVICE_PREFIX = 'AbstractServer';

    /**
     * @var string The name of the client service
     */
    private $clientIdentifier;

    /**
     * @param ConfigInterface $config Configuration
     * @param string $clientIdentifier The name of the client service
     * @param array $types The types the service knows about
     * @param string $description The description of the service
     */
    public function __construct(ConfigInterface $config, $clientIdentifier, array $types, $description)
    {
        $this->clientIdentifier = $clientIdentifier;
        $serverIdentifier = $config->get('serverClassName');
        if (empty($serverIdentifier)) {
            $serverIdentifier = self::SERVER_SERVICE_PREFIX . $clientIdentifier;
        }
        parent::__construct($config, $serverIdentifier, $types, $description);
    }

    /**
     * Generates the class if not already generated
     */
    public function generateClass()
    {
        $serverName = $this->identifier;

        // Generate a valid classname
        $serverName = Validator::validateClass($serverName, $this->config->get('namespaceName'));

        // uppercase the name
        $serverName = ucfirst($serverName);

        $clientName = $this->clientIdentifier;

        // Generate a valid classname
        $clientName = Validator::validateClass($clientName, $this->config->get('namespaceName'));

        // uppercase the name
        $clientName = ucfirst($clientName);

        // Create the class object
        $comment = new PhpDocComment($this->description);
        $this->class = new PhpClass($serverName, false, $this->config->get('soapServerClass'), $comment, false, true);

        // Create the constructor
        $comment = new PhpDocComment();
        $comment->addParam(PhpDocElementFactory::getParam('array', 'options', 'A array of config values'));
        $comment->addParam(PhpDocElementFactory::getParam('string', 'wsdl', 'The wsdl file to use'));

        $source = '
  foreach (' . $clientName . '::$classmap as $key => $value) {
    if (!isset($options[\'classmap\'][$key])) {
      $options[\'classmap\'][$key] = $value;
    }
  }' . PHP_EOL;
        $source .= '  $options = array_merge(' . var_export($this->config->get('soapServerOptions'), true) . ', $options);' . PHP_EOL;
        $source .= '  $this->computedOptions = $options;' . PHP_EOL;
        $source .= '  parent::__construct($wsdl, $options);' . PHP_EOL;
        $source .= '  $this->setObject($this);' . PHP_EOL;

        $function = new PhpFunction('public', '__construct', 'array $options = array(), $wsdl = \'' . $this->config->get('inputFile') . '\'', $source, $comment);

        // Add the constructor
        $this->class->addFunction($function);

        // Generate the computed options
        $name = 'computedOptions';
        $comment = new PhpDocComment();
        $comment->setVar(PhpDocElementFactory::getVar('array', $name, 'The computed options'));
        $var = new PhpVariable('protected', $name, '', $comment);

        // Add the computed options variable
        $this->class->addVariable($var);

        // Add all methods
        foreach ($this->operations as $operation) {
            $name = Validator::validateOperation($operation->getName());

            $comment = new PhpDocComment($operation->getDescription());
            $comment->setReturn(PhpDocElementFactory::getReturn($operation->getReturns(), ''));

            foreach ($operation->getParams() as $param => $hint) {
                $arr = $operation->getPhpDocParams($param, $this->types);
                $comment->addParam(PhpDocElementFactory::getParam($arr['type'], $arr['name'], $arr['desc']));
            }

            $paramStr = $operation->getParamString($this->types);

            $function = new PhpFunction('abstract public', $name, $paramStr, null, $comment);

            if ($this->class->functionExists($function->getIdentifier()) == false) {
                $this->class->addFunction($function);
            }
        }
    }
}
