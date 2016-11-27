<?php

namespace Wsdl2PhpGenerator\Xml;

/**
 * Provides context for manage loading schemas.
 */
class SchemaContext
{
    private $loadedUrls = array();

    /**
     * Determines whether to load the schema.
     *
     * @param string $xsdUrl
     *
     * @return bool
     */
    public function needToLoad($xsdUrl)
    {
        return false === in_array($xsdUrl, $this->loadedUrls);
    }

    /**
     * Registers the schema to avoid cyclical imports.
     *
     * @param string $xsdUrl
     */
    public function loaded($xsdUrl)
    {
        if (!in_array($xsdUrl, $this->loadedUrls)) {
            $this->loadedUrls[] = $xsdUrl;
        }
    }
}
