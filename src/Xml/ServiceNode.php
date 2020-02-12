<?php

/*
 * This file is part of the WSDL2PHPGenerator package.
 * (c) WSDL2PHPGenerator.
 */

namespace Wsdl2PhpGenerator\Xml;

/**
 * An XML node which represents a SOAP service.
 */
class ServiceNode extends DocumentedNode
{
    /**
     * Returns the name of the service.
     *
     * @return string the service name
     */
    public function getName()
    {
        return $this->element->getAttribute('name');
    }
}
