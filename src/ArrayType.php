<?php

/**
 * @package Generator
 */
namespace Wsdl2PhpGenerator;

use \Exception;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

/**
 * ArrayType
 *
 * @package Wsdl2PhpGenerator
 */
class ArrayType extends ComplexType
{
    /**
     * Field with array
     *
     * @var Variable
     */
    protected $field;

    /**
     * Type of array elements
     *
     * @var string
     */
    protected $arrayOf;

    /**
     * Implements the loading of the class object
     *
     * @throws Exception if the class is already generated(not null)
     */
    protected function generateClass()
    {
        parent::generateClass();

        // If it is child type, fallback to ComplexType. Can check this only when all
        // types are loaded. See Generator->loadTypes();
        if ($this->getBaseTypeClass() === null) {
            $this->implementArrayInterfaces();
        }
    }

    protected function implementArrayAccess()
    {
        $this->class->setImplementedInterfaces(
            array_merge(
                ['\\ArrayAccess'],
                $this->class->getImplementedInterfaces()
            )
        );
        $description = 'ArrayAccess implementation';

        // offsetExists
        $this->class->addMethodFromGenerator(
            (new MethodGenerator())
                ->setName('offsetExists')
                ->setFlags(MethodGenerator::FLAG_PUBLIC)
                ->setDocBlock(
                    (new DocBlockGenerator())
                        ->setShortDescription($description)
                        ->setTag(new ParamTag('offset', 'mixed', 'An offset to check for'))
                        ->setTag(new ReturnTag('boolean', 'true on success or false on failure'))
                )
                ->setParameter(new ParameterGenerator('offset'))
                ->setBody('return isset($this->' . $this->field->getName() . '[$offset]);')
        );

        // offsetGet
        $this->class->addMethodFromGenerator(
            (new MethodGenerator())
                ->setName('offsetGet')
                ->setFlags(MethodGenerator::FLAG_PUBLIC)
                ->setDocBlock(
                    (new DocBlockGenerator())
                        ->setShortDescription($description)
                        ->setTag(new ParamTag('offset', 'mixed', 'The offset to retrieve'))
                        ->setTag(new ReturnTag($this->arrayOf))
                )
                ->setParameter(new ParameterGenerator('offset'))
                ->setBody('return $this->' . $this->field->getName() . '[$offset];')
        );

        // offsetSet
        $this->class->addMethodFromGenerator(
            (new MethodGenerator())
                ->setName('offsetSet')
                ->setFlags(MethodGenerator::FLAG_PUBLIC)
                ->setDocBlock(
                    (new DocBlockGenerator())
                        ->setShortDescription($description)
                        ->setTag(new ParamTag('offset', 'mixed', 'The offset to assign the value to'))
                        ->setTag(new ParamTag('value', $this->arrayOf, 'The value to set'))
                        ->setTag(new ReturnTag('void'))
                )
                ->setParameter(new ParameterGenerator('offset'))
                ->setParameter(new ParameterGenerator('value'))
                ->setBody(
                    '  if (!isset($offset)) {' . PHP_EOL .
                    '    $this->' . $this->field->getName() . '[] = $value;' . PHP_EOL .
                    '  } else {' . PHP_EOL .
                    '    $this->' . $this->field->getName() . '[$offset] = $value;' . PHP_EOL .
                    '  }'
                )
        );

        // offsetUnset
        $this->class->addMethodFromGenerator(
            (new MethodGenerator())
                ->setName('offsetUnset')
                ->setFlags(MethodGenerator::FLAG_PUBLIC)
                ->setDocBlock(
                    (new DocBlockGenerator())
                        ->setShortDescription($description)
                        ->setTag(new ParamTag('offset', 'mixed', 'The offset to unset'))
                        ->setTag(new ReturnTag('void'))
                )
                ->setParameter(new ParameterGenerator('offset'))
                ->setBody('unset($this->' . $this->field->getName() . '[$offset]);')
        );
    }

    protected function implementIterator()
    {
        $this->class->setImplementedInterfaces(
            array_merge(
                ['\\Iterator'],
                $this->class->getImplementedInterfaces()
            )
        );
        $description = 'Iterator implementation';

        // current
        $this->class->addMethodFromGenerator(
            (new MethodGenerator())
                ->setName('current')
                ->setFlags(MethodGenerator::FLAG_PUBLIC)
                ->setDocBlock(
                    (new DocBlockGenerator())
                        ->setShortDescription($description)
                        ->setTag(new ReturnTag($this->arrayOf, 'Return the current element'))
                )
                ->setBody('return current($this->' . $this->field->getName() . ');')
        );

        // next
        $this->class->addMethodFromGenerator(
            (new MethodGenerator())
                ->setName('next')
                ->setFlags(MethodGenerator::FLAG_PUBLIC)
                ->setDocBlock(
                    (new DocBlockGenerator())
                        ->setShortDescription($description . PHP_EOL . 'Move forward to next element')
                        ->setTag(new ReturnTag('void'))
                )
                ->setBody('next($this->' . $this->field->getName() . ');')
        );

        // key
        $this->class->addMethodFromGenerator(
            (new MethodGenerator())
                ->setName('key')
                ->setFlags(MethodGenerator::FLAG_PUBLIC)
                ->setDocBlock(
                    (new DocBlockGenerator())
                        ->setShortDescription($description)
                        ->setTag(new ReturnTag('string|null', 'Return the key of the current element or null'))
                )
                ->setBody('return key($this->' . $this->field->getName() . ');')
        );

        // valid
        $this->class->addMethodFromGenerator(
            (new MethodGenerator())
                ->setName('valid')
                ->setFlags(MethodGenerator::FLAG_PUBLIC)
                ->setDocBlock(
                    (new DocBlockGenerator())
                        ->setShortDescription($description)
                        ->setTag(new ReturnTag('boolean', 'Return the validity of the current position'))
                )
                ->setBody('return $this->key() !== null;')
        );

        // rewind
        $this->class->addMethodFromGenerator(
            (new MethodGenerator())
                ->setName('rewind')
                ->setFlags(MethodGenerator::FLAG_PUBLIC)
                ->setDocBlock(
                    (new DocBlockGenerator())
                        ->setShortDescription($description . PHP_EOL . 'Rewind the Iterator to the first element')
                        ->setTag(new ReturnTag('void'))
                )
                ->setBody('reset($this->' . $this->field->getName() . ');')
        );
    }

    protected function implementCountable()
    {
        $this->class->setImplementedInterfaces(
            array_merge(
                ['\\Countable'],
                $this->class->getImplementedInterfaces()
            )
        );

        $this->class->addMethodFromGenerator(
            (new MethodGenerator())
                ->setName('count')
                ->setFlags(MethodGenerator::FLAG_PUBLIC)
                ->setDocBlock(
                    (new DocBlockGenerator())
                        ->setShortDescription('Countable implementation')
                        ->setTag(new ReturnTag('int', 'Return count of elements'))
                )
                ->setBody('return count($this->' . $this->field->getName() . ');')
        );
    }

    protected function implementArrayInterfaces()
    {
        $members = array_values($this->members);
        $this->field = $members[0];
        $this->arrayOf = substr($this->field->getType(), 0, -2);

        $this->implementArrayAccess();
        $this->implementIterator();
        $this->implementCountable();
    }
}
