<?php

/**
 * @package ZendCode
 */
namespace Wsdl2PhpGenerator\ZendCode;

use Zend\Code\Generator\DocBlock\Tag\ParamTag;

class VarTag extends ParamTag
{
    /**
     * @return string
     */
    public function generate()
    {
        $output = '@var'
            . ((!empty($this->types)) ? ' ' . $this->getTypesAsString() : '')
            . ((!empty($this->variableName)) ? ' $' . $this->variableName : '')
            . ((!empty($this->description)) ? ' ' . $this->description : '');

        return $output;
    }
}
