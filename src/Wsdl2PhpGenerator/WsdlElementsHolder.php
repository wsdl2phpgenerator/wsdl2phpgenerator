<?php

namespace Wsdl2PhpGenerator;


class WsdlElementsHolder {
    /**
     * @var Service
     */
    private $service;
    /**
     * @var Service
     */
    private $baseService;
    /**
     * An array of Type objects that represents the types in the service
     *
     * @var Type[]
     */
    private $types = array();

    function __construct() {
        $this->service = null;
        $this->baseService = null;
    }

    public function addType($typeName, $type) {
        $this->types[$typeName] = $type;
    }

    /**
     * @param array|null $methods
     */
    public function filterByMethods($methods) {
        if (!$methods) {
            return $this;
        }
        if (!$this->baseService) {
            throw new \RuntimeException("Service not initiated.");
        }
        $holder = new self();
        $holder->setService(clone ($this->baseService));
        foreach ($methods as $method) {
            $operation = $this->getService()->getOperation($method);
            $holder->getService()->addOperationObject($operation);
            foreach ($operation->getParams() as $param) {
                $holder->addType($param, $this->getType($param));
            }
            $returns = $operation->getReturns();
            $holder->addType($returns, $this->getType($returns));
            $this->combineTypesForMethod($holder, $holder->getTypes());
        }
        $holder->getService()->setTypes($holder->getTypes());
        return $holder;
    }

    /**
     * @param self $holder
     * @param array $arrayOfTypes
     */
    private function combineTypesForMethod($holder, $arrayOfTypes) {
        $foundedTypes = array();
        /**
         * @var Type $type
         */
        foreach ($arrayOfTypes as $name => $type) {
            if ($type instanceof ComplexType) {
                /** @var ComplexType $type */
                $members = $type->getMembers();
                foreach ($members as $member) {
                    /** @var Variable $member */
                    $memberTypeName = str_replace('[]', '', $member->getType());
                    $memberType = $this->getType($memberTypeName);
                    if (!$memberType || $holder->hasType($memberTypeName)) { continue; }
                    $holder->addType($memberTypeName, $memberType);
                    $foundedTypes[$memberTypeName] = $memberType;
                }
            }
        }
        if (!$foundedTypes) { return; }
        $this->combineTypesForMethod($holder, $foundedTypes);
    }

    /**
     * @return Service
     */
    public function getService() {
        return $this->service;
    }

    /**
     * @param string $type
     *
     * @return null|Type
     */
    public function getType($type) {
        return $this->hasType($type) ? $this->types[$type] : null;
    }
    /**
     * @return Type[]
     */
    public function getTypes() {
        return $this->types;
    }

    /**
     * @param Service $service
     */
    public function setService($service) {
        $this->service = $service;
        $this->baseService = clone $service;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function hasType($type) {
        return isset($this->types[$type]);
    }
}