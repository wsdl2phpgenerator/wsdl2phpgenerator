<?php

/**
 * @package Wsdl2PhpGenerator
 */

/**
 * Very stupid datatype to use instead of array
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class DocumentationManager
{
    /**
     *
     * @var string The documentation for the service
     */
    private $serviceDescription;

    /**
     * The key is the function name
     *
     * @var array An array with strings with function descriptions
     */
    private $functionDescriptions;

    public function __construct()
    {
        $this->serviceDescription = '';
        $this->functionDescriptions = array();
    }

    /**
     * Loads all documentation into the instance
     *
     * @param DOMDocument $dom The wsdl file dom document
     */
    public function loadDocumentation(DOMDocument $dom)
    {
        $docList = $dom->getElementsByTagName('documentation');

        foreach ($docList as $item) {
            if ($item->parentNode->localName == 'service') {
                $this->serviceDescription = trim($item->parentNode->nodeValue);
            } elseif ($item->parentNode->localName == 'operation') {
                $name = $item->parentNode->getAttribute('name');
                $this->setFunctionDescription($name, trim($item->nodeValue));
            }
        }
    }

    /**
     *
     * @return string The documentation for the service
     */
    public function getServiceDescription()
    {
        return $this->serviceDescription;
    }

    /**
     *
     * @param string $serviceDescription The new documentation
     */
    public function setServiceDescription($serviceDescription)
    {
        $this->serviceDescription = $serviceDescription;
    }

    /**
     *
     * @param string $function The name of the function
     * @param string $description The documentation
     */
    public function setFunctionDescription($function, $description)
    {
        $this->functionDescriptions[$function] = $description;
    }

    /**
     *
     * @param string $function
     * @return string The description
     */
    public function getFunctionDescription($function)
    {
        $ret = '';
        $ret = @$this->functionDescriptions[$function];

        return $ret;
    }
}
