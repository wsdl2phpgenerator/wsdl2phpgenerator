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

        parent::__construct();
    }

    /**
     * Returns whether the type is an array.
     *
     * @return bool If the type is an array.
     */
    public function isArray()
    {
        return substr($this->name, -2, 2) == '[]' || substr($this->name, 0, 7) == 'ArrayOf';
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
            if ($element->getAttribute('name') == $name && $element->getAttribute('maxOccurs') == 'unbounded') {
                return true;
            }
        }
        return false;
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
        // Checking the number of lines in the type filters out simple types, enumerations and patterns.
        // There might be a better way to go about this but this approach was used in previous versions so we keep it
        // that way for now.
        return count($this->getWsdlLines()) > 1;
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
