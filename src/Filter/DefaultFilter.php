<?php
namespace Wsdl2PhpGenerator\Filter;


use Wsdl2PhpGenerator\ServiceInterface;

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
    public function filter(ServiceInterface $service)
    {
        return $service;
    }
}
