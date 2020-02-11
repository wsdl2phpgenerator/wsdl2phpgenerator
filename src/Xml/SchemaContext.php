<?php

namespace Wsdl2PhpGenerator\Xml;

use Wsdl2PhpGenerator\ConfigInterface;
use Wsdl2PhpGenerator\StreamContextFactory;

/**
 * Provides context for manage loading schemas.
 */
class SchemaContext
{
    private $config;
    private $streamContextFactory;
    private $loadedUrls = array();

    public function __construct(ConfigInterface $config, StreamContextFactory $streamContextFactory = null)
    {
        if (null === $streamContextFactory) {
            $streamContextFactory = new StreamContextFactory();
        }

        $this->config = $config;
        $this->streamContextFactory = $streamContextFactory;
    }

    public function getStreamContext()
    {
        return $this->streamContextFactory->create($this->config);
    }

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
