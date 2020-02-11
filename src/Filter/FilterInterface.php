<?php

/*
 * This file is part of the WSDL2PHPGenerator package.
 * (c) WSDL2PHPGenerator.
 */

namespace Wsdl2PhpGenerator\Filter;

use Wsdl2PhpGenerator\Service;

/**
 * A filter implementation allows modification of a service.
 *
 * This can be used the alter which classes will be generated.
 */
interface FilterInterface
{
    /**
     * Filter a service.
     *
     * @param Service $service the initial service
     *
     * @return Service the altered service
     */
    public function filter(Service $service);
}
