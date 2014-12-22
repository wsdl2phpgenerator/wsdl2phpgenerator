<?php
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
     * @param Service $service The initial service.
     * @return Service The altered service.
     */
    public function filter(Service $service);
}
