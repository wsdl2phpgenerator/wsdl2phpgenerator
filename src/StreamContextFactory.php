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
        $settings = $config->get('soapClientOptions');

        // Basic authentication.
        if (!empty($settings['authentication'])
            && $settings['authentication'] == SOAP_AUTHENTICATION_DIGEST) {
            throw new InvalidOptionsException('Digest authentication is not supported.');
        }

        if (isset($settings['login']) && isset($settings['password'])) {
            $options['http']['header'][] = 'Authorization: Basic ' .
                base64_encode($settings['login'] . ':' . $settings['password']);
        }

        // Proxy configuration.
        if (!empty($settings['proxy_host'])) {
            $options['http']['proxy'] = $settings['proxy_host'];

            if (!empty($settings['proxy_port'])) {
                $options['http']['proxy'] .= ':' . $settings['proxy_port'];
            }

            if (isset($settings['proxy_login']) && isset($settings['proxy_password'])) {
                $options['http']['header'][] = 'Proxy-Authorization: Basic ' .
                    base64_encode($settings['proxy_login'] . ':' . $settings['proxy_password']);
            }
        }

        return stream_context_create($options);
    }
}
