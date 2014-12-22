<?php
namespace Wsdl2PhpGenerator\Filter;


use Wsdl2PhpGenerator\Service;

/**
 * Default filter implementation.
 *
 * It does not do anything.
 */
class DefaultFilter implements FilterInterface
{
    /**
     * @inheritdoc
     */
    public function filter(Service $service)
    {
        return $service;
    }
}
