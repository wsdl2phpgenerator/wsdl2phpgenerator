<?php


namespace Wsdl2PhpGenerator\Xml;

/**
 * An XmlNode which contains documentation of itself.
 */
abstract class DocumentedNode extends XmlNode
{

    /**
     * Retrieves the documentation for the node.
     *
     * @return string The documentation.
     */
    public function getDocumentation()
    {
        $documentation = null;
        $documentationNodes = $this->element->getElementsByTagName('documentation');
        if ($documentationNodes->length > 0) {
            $documentation = $documentationNodes->item(0)->nodeValue;
        }
        return $documentation;
    }
}
