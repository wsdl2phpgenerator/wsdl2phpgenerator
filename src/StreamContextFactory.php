<?php


namespace Wsdl2PhpGenerator;

/**
 * Factory class for creating stream contexts when accessing external resources.
 */
class StreamContextFactory
{

    /**
     * Creates a stream context based on the provided configuration.
     *
     * @param ConfigInterface $config The configuration.
     *
     * @return resource A stream context based on the provided configuration.
     */
    public function create(ConfigInterface $config)
    {
        $options = array();

        $proxy = $config->get('proxy');
        if (is_array($proxy)) {
            $options = array(
                'http' => array(
                    'proxy' => $proxy['proxy_host'] . ':' . $proxy['proxy_port']
                )
            );
            if (isset($proxy['proxy_login']) && isset($proxy['proxy_password'])) {
                // Support for proxy authentication is untested. The current implementation is based on
                // http://php.net/manual/en/function.stream-context-create.php#74431.
                $options['http']['header'] =  array(
                    'Proxy-Authorization: Basic ' .
                    base64_encode($proxy['proxy_login'] . ':' . $proxy['proxy_password'])
                );
            }
        }

        return stream_context_create($options);
    }
}
