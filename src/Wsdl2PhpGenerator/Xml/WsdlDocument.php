<?php


namespace Wsdl2PhpGenerator\Xml;


use Exception;
use SoapClient;
use SoapFault;
use Wsdl2PhpGenerator\ConfigInterface;

/**
 * The WSDL document represents a file which is used to access a SOAP service.
 *
 * This is the key point for extracting information about the service, which functions it exposes and which data types
 * are used when calling them.
 */
class WsdlDocument extends SchemaDocument
{

    /**
     * An instance of a PHP SOAP client based on the WSDL file
     *
     * @var SoapClient
     */
    protected $soapClient;

    /**
     * The configuration.
     *
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(ConfigInterface $config, $wsdlUrl)
    {
        $this->config = $config;

        try {
            $this->soapClient = new SoapClient($wsdlUrl, $this->config->getOptionFeatures());
            parent::__construct($wsdlUrl);
        } catch (SoapFault $e) {
            throw new Exception('Unable to load WSDL: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns representations of all the data types used when working with the SOAP service.
     *
     * @return TypeNode[] Data types related to the service.
     */
    public function getTypes()
    {
        $types = array();

        $typeStrings = $this->soapClient->__getTypes();
        foreach ($typeStrings as $typeString) {
            $type = new TypeNode($typeString);
            $element = $this->findTypeElement($type->getName());
            if (!empty($element)) {
                $type->setElement($this->document, $element);
            }

            $types[] = $type;
        }

        return $types;
    }

    /**
     * Returns a representation of the service described by the WSDL file.
     *
     * @return ServiceNode The service described by the WSDL.
     */
    public function getService()
    {
        $serviceNodes = $this->element->getElementsByTagName('service');
        if ($serviceNodes->length > 0) {
            return new ServiceNode($this->document, $serviceNodes->item(0));
        }
        return null;
    }

    /**
     * Returns representations of all the operations exposed by the service.
     *
     * @return OperationNode[] The operations exposed by the service.
     */
    public function getOperations()
    {
        $functions = array();
        foreach ($this->soapClient->__getFunctions() as $functionString) {
            $function = new OperationNode($functionString);
            $functionNodes = $this->xpath('//wsdl:operation[@name="' . $function->getName() . '"]');
            if ($functionNodes->length > 0) {
                $function->setElement($this->document, $functionNodes->item(0));
                $functions[] = $function;
            }
        }
        return $functions;
    }
}
