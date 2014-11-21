<?php

namespace Wsdl2PhpGenerator\Filter;


use Wsdl2PhpGenerator\ComplexType;
use Wsdl2PhpGenerator\ConfigInterface;
use Wsdl2PhpGenerator\Service;

class ServiceOperationFilter implements  FilterInterface
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
    function __construct($config) {
        $this->config = $config;
        $this->methods = $config->get('methodNames');
    }

    /**
     * @param Service $service
     *
     * @return Service
     */
    public function filter($service)
    {
        $types = array();
        $operations = array();
        foreach ($this->methods as $method) {
            $operation = $service->getOperation($method);
            if (!$operation) { continue; }
            foreach ($operation->getParams() as $param => $hint) {
                $types[$param] = $service->getType($param);
            }
            $returns = $operation->getReturns();
            $types[$operation->getReturns()] = $service->getType($returns);
            $types = array_merge($types, $this->calculateInheretedTypes($service, $types, $types));
            $operations[] = $operation;
        }
        $filteredService = new Service($this->config, $service->getIdentifier(), $types, $service->getDescription());
        foreach ($operations as $operation) {
            $filteredService->addOperation($operation);
        }
        return $filteredService;
    }

    /**
     * @param Service $service
     * @param Type[] $finalTypes
     * @param Type[] $typesToProccess
     */
    private function calculateInheretedTypes($service, $finalTypes, $typesToProccess) {
        $foundedTypes = array();
        /** @var Type $type */
        foreach ($typesToProccess as $name => $type) {
            if ($type instanceof ComplexType) {
                /** @var ComplexType $type */
                $members = $type->getMembers();
                foreach ($members as $member) {
                    /** @var Variable $member */
                    // @HACK: Consider how to get type for array
                    $memberTypeName = str_replace('[]', '', $member->getType());
                    $memberType = $service->getType($memberTypeName);
                    if (!$memberType || isset($finalTypes[$memberTypeName])) {
                        continue;
                    }
                    $finalTypes[$memberTypeName] = $memberType;
                    $foundedTypes[$memberTypeName] = $memberType;
                }
            }
            $baseType = $type->getBaseType();
            if ($baseType && $baseType instanceof ComplexType) {
                $finalTypes[$baseType->getDatatype()] = $baseType;
                $foundedTypes[$baseType->getDatatype()] = $baseType;
            }
        }
        if (!$foundedTypes) {
            return $finalTypes;
        }
        return $this->calculateInheretedTypes($service, $finalTypes, $foundedTypes);
    }
}