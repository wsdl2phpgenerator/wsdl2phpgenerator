<?php


namespace Wsdl2PhpGenerator\Xml;

/**
 * An XML node which represents a specific type of element used when interacting with a SOAP service.
 */
class TypeNode extends XmlNode
{

    /**
     * The original version of the type as returned by the SOAP client.
     *
     * @var string
     */
    protected $wsdlType;

    /**
     * The name of the type.
     *
     * @var string
     */
    protected $name;

    /**
     * The datatype of the value represented by the element.
     *
     * @var string
     */
    protected $restriction;

    /**
     * @param string $wsdlType The type as represented by the SOAP client.
     */
    public function __construct($wsdlType)
    {
        $this->wsdlType = $wsdlType;

        // The first line of the WSDL type contains the type name and restriction. Extract them.
        $lines = $this->getWsdlLines();
        $firstLineElements = explode(" ", $lines[0]);
        $this->restriction = $firstLineElements[0];
        $this->name = $firstLineElements[1];
        if (substr($this->name, -2, 2) == '[]') {
            $this->name = substr($this->name, 0, -2);
        }

        parent::__construct();
    }

    /**
     * Returns whether a sub element of the type may be undefined for the type.
     *
     * @param string $name The name of the sub element.
     * @return bool Whether the sub element may be undefined for the type.
     */
    public function isElementNillable($name)
    {
        foreach ($this->element->getElementsByTagName('element') as $element) {
            if ($element->getAttribute('name') == $name && $element->getAttribute('nillable') == true) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns whether a sub element of the type is an array of elements.
     * @param $name string The name of the sub element
     * @return bool Whether the sub element is an array of elements.
     */
    public function isElementArray($name)
    {
        foreach ($this->element->getElementsByTagName('element') as $element) {
            if ($element->getAttribute('name') == $name &&
                  ($element->getAttribute('maxOccurs') == 'unbounded'
                    || $element->getAttribute('maxOccurs') >= 2)
              ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the minOccurs value of the element.
     * @param $name string The name of the sub element
     * @return int the minOccurs value of the element
     */
    public function getElementMinOccurs($name)
    {
        foreach ($this->element->getElementsByTagName('element') as $element) {
            if ($element->getAttribute('name') == $name) {
                $minOccurs = $element->getAttribute('minOccurs');
                if ($minOccurs === '') {
                    return null;
                }
                return (int) $minOccurs;
            }
        }
        return null;
    }

    /**
     * Returns the base type for the type.
     *
     * This is used to model inheritance between types.
     *
     * @return string The name of the base type for the type.
     */
    public function getBase()
    {
        $base = null;

        $extensions = $this->element->getElementsByTagName('extension');
        if ($extensions->length > 0) {
            $base = $this->cleanNamespace($extensions->item(0)->getAttribute('base'));
        }

        return $base;
    }

    /**
     * Returns the sub elements of the type.
     *
     * The elements are returned as an array where keys are names of sub elements and values are their type.
     *
     * @return array An array of sub element names and types.
     */
    public function getParts()
    {
        $wsdlLines = $this->getWsdlLines();

        $parts = array();

        // If array is defied as inherited from array type it have only one line and looks like "Type ArrayOfType[]"
        if (sizeof($wsdlLines) == 1 && substr($wsdlLines[0], -2, 2) == '[]') {
            list($typeName, $name) = explode(" ", $wsdlLines[0]);
            $name = substr($name, 0, -2);
            $typeName .= '[]';

            $parts[$name] = $typeName;
        }

        for ($i = 1; $i < sizeof($wsdlLines) - 1; $i++) {
            $wsdlLines[$i] = trim($wsdlLines[$i]);
            list($typeName, $name) = explode(" ", substr($wsdlLines[$i], 0, strlen($wsdlLines[$i]) - 1));

            if ($this->isElementArray($name)) {
                $typeName .= '[]';
            }

            $parts[$name] = $typeName;
        }

        return $parts;
    }

    /**
     * Returns the pattern which the type represents if any.
     *
     * @return string The pattern.
     */
    public function getPattern()
    {
        $pattern = null;

        if ($patternNodes = $this->element->getElementsByTagName('pattern')) {
            if ($patternNodes->length > 0) {
                $pattern = $patternNodes->item(0)->getAttribute('value');
            }
        }

        return $pattern;
    }

    /**
     * Returns an array of values that the type may have if the type is an enumeration.
     *
     * @return string[] The valid enumeration values.
     */
    public function getEnumerations()
    {
        $enums = array();
        foreach ($this->element->getElementsByTagName('enumeration') as $enum) {
            $enums[] = $enum->getAttribute('value');
        };
        return $enums;
    }

    /**
     * Returns the value the type may have.
     *
     * @return string the value of the type.
     */
    public function getRestriction()
    {
        return $this->restriction;
    }

    /**
     * Returns the name of the type.
     *
     * @return string The type name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns whether the type is complex ie. that is may contain sub elements or not.
     *
     * @return bool Whether the type is complex.
     */
    public function isComplex()
    {
        // If array is defied as inherited from array type it has restricton to array elements type, but still is complexType
        return
          $this->restriction == 'struct' ||
          $this->element->localName == 'complexType';
    }

    /**
     * Returns whether the type is an array.
     *
     * @return bool If the type is an array.
     */
    public function isArray()
    {
        $parts = $this->getParts();

        // Array types are complex types with one element, their names begins with 'ArrayOf'.
        // So if not - that's not array. Only field must be array also.
        return $this->isComplex()
            && count($parts) == 1
            && (substr($this->name, 0, 7) == 'ArrayOf')
            && substr(reset($parts), -2, 2) == '[]';
    }

    /**
     * Returns whether the type is abstract.
     *
     * @return bool Whether the type is abstract.
     */
    public function isAbstract()
    {
        return $this->element->hasAttribute('abstract')
            && $this->element->getAttribute('abstract') == 'true';
    }

    /**
     * Returns the lines of WSDL type.
     *
     * @return string[] The lines of the WSDL type.
     */
    protected function getWsdlLines()
    {
        $newline = (strpos($this->wsdlType, "\r\n") ? "\r\n" : "\n");
        return explode($newline, $this->wsdlType);
    }
}
