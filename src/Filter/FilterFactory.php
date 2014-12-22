<?php
namespace Wsdl2PhpGenerator\Filter;


use Wsdl2PhpGenerator\ConfigInterface;

/**
 * Factory class for retrieving filters.
 */
class FilterFactory
{
    /**
     * Returns a filter matching the provided configuration.
     *
     * @param ConfigInterface $config The configuration to create a filter for.
     * @return FilterInterface A matching filter.
     */
    public function create(ConfigInterface $config)
    {
        $operationNames = $config->get('operationNames');
        if (!empty($operationNames)) {
            return new ServiceOperationFilter($config);
        } else {
            return new DefaultFilter();
        }
    }
}
