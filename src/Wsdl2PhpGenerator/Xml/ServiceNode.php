<?php


namespace Wsdl2PhpGenerator\Xml;

/**
 * An XML node which represents a SOAP service.
 */
class ServiceNode extends DocumentedNode
{

    /**
     * Returns the name of the service.
     *
     * @return string The service name.
     */
    public function getName()
    {
        return $this->element->getAttribute('name');
    }
}
