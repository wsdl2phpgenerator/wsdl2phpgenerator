<?php

namespace Wsdl2PhpGenerator\Filter;

use Wsdl2PhpGenerator\ComplexType;
use Wsdl2PhpGenerator\ConfigInterface;
use Wsdl2PhpGenerator\Service;

/**
 * Filter that leaves only selected operations. Also removes not used types
 */
class ServiceOperationFilter implements FilterInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var array
     */
    private $methods;

    /**
     * @param ConfigInterface $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->methods = $config->get('methodNames');
    }

    /**
     * @param Service $service
     *
     * @return Service
     */
    public function filter(Service $service)
    {
        $types = array();
        $operations = array();
        foreach ($this->methods as $method) {
            $operation = $service->getOperation($method);
            if (!$operation) {
                continue;
            }
            // Discover types used in params
            foreach ($operation->getParams() as $param => $hint) {
                $arr = $operation->getPhpDocParams($param, $service->getTypes());
                $type = $service->getType($arr['type']);
                if (!empty($type)) {
                    $types[$type->getIdentifier()] = $type;
                }
            }
            // Discover types used in returns
            $returns = $operation->getReturns();
            $types[$operation->getReturns()] = $service->getType($returns);
            $types = $this->calculateInheretedTypes($service, $types, $types);
            $operations[] = $operation;
        }
        $filteredService = new Service($this->config, $service->getIdentifier(), $types, $service->getDescription());
        // Pull created service with operations
        foreach ($operations as $operation) {
            $filteredService->addOperation($operation);
        }
        return $filteredService;
    }

    /**
     * Function to find all needed types
     *
     * @param Service $service Source service with all types and operations
     * @param Type[] $finalTypes Types for selected operations
     * @param Type[] $typesToProcess Types that we should process in iteration
     */
    private function calculateInheretedTypes($service, $finalTypes, $typesToProcess)
    {
        $foundedTypes = array();
        /** @var Type $type */
        foreach ($typesToProcess as $name => $type) {
            if (empty($type)) {
                continue;
            }
            // Process only complex types.
            if ($type instanceof ComplexType) {
                /** @var ComplexType $type */
                $members = $type->getMembers();
                foreach ($members as $member) {
                    /** @var Variable $member */
                    // Remove array mark from type name
                    $memberTypeName = str_replace('[]', '', $member->getType());
                    $memberType = $service->getType($memberTypeName);
                    if (!$memberType || isset($finalTypes[$memberTypeName])) {
                        continue;
                    }
                    $finalTypes[$memberTypeName] = $memberType;
                    $foundedTypes[$memberTypeName] = $memberType;
                }
                // Process Base type
                $baseType = $type->getBaseType();
                if ($baseType && $baseType instanceof ComplexType) {
                    $finalTypes[$baseType->getDatatype()] = $baseType;
                    $foundedTypes[$baseType->getDatatype()] = $baseType;
                }
            }
        }
        if (!$foundedTypes) {
            return $finalTypes;
        }
        return $this->calculateInheretedTypes($service, $finalTypes, $foundedTypes);
    }
}
