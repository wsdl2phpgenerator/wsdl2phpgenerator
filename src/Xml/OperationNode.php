<?php


namespace Wsdl2PhpGenerator\Xml;

/**
 * An XML node which represents an operation to be executed through a SOAP service.
 */
class OperationNode extends DocumentedNode
{

    /**
     * The original version of the operation as returned by the SOAP client.
     *
     * Note that the PHP SOAP client refers to these as functions.
     *
     * @var string
     */
    protected $wsdlFunction;

    /**
     * The name of the operation.
     *
     * @var string
     */
    protected $name;

    /**
     * The parameters for invoking the operation.
     *
     * They are represented as a string in the format "type1 parameter1, type2 parameter2" etc.
     *
     * @var string Parameter types and names separated by comma "type1 parameter1, type2 parameter2".
     */
    protected $params;

    /**
     * The name of the return type value.
     *
     * @var string The return type.
     */
    protected $returns;

    public function __construct($wsdlFunction)
    {
        $this->wsdlFunction = $wsdlFunction;
        $matches = array();
        if (preg_match(
            // Look for definitions in the format:
            // return_type method_name(param_type1 param1, param_type2 param2)
            '/^(\w[\w\d_.]*) (\w[\w\d_]*)\(([\w\$\d,_. ]*)\)$/u',
            $this->wsdlFunction,
            $matches
        )) {
            $this->returns = $matches[1];
            $this->name = $matches[2];
            $this->params = $matches[3];
        } elseif (preg_match(
            // @TODO Document when this case is triggered and what the difference is to the case above.
            '/^(list\([\w\$\d,_. ]*\)) (\w[\w\d_]*)\(([\w\$\d,_. ]*)\)$/u',
            $this->wsdlFunction,
            $matches
        )) {
            $this->returns = $matches[1];
            $this->name = $matches[2];
            $this->params = $matches[3];
        }

        parent::__construct();
    }

    /**
     * Returns the name of the operation
     *
     * @return string The operation name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns a string representing the parameters of the operation.
     *
     * @return string Parameters in the format "type1 param1, typ2 param2".
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Returns the name of the return type for the operation.
     *
     * @return string The operation return type.
     */
    public function getReturns()
    {
        return $this->returns;
    }
}
