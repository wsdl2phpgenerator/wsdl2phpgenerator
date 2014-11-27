<?php

namespace Wsdl2PhpGenerator;

use InvalidArgumentException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Wsdl2PhpGenerator\ConfigInterface;

/**
 * This class contains configurable key/value pairs.
 *
 * @package Wsdl2PhpGenerator
 */
class Config implements ConfigInterface
{
    /**
     * @var array The actual key/value pairs.
     */
    protected $options;

    public function __construct(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    /**
     * Get a value from the configuration by key.
     *
     * @param $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get($key)
    {
        if (!array_key_exists($key, $this->options)) {
            throw new InvalidArgumentException(sprintf('The key %s does not exist.', $key));
        }

        return $this->options[$key];
    }

    /**
     * Set or overwrite a configuration key with a given value.
     *
     * @param $key
     * @param $value
     * @return $this|ConfigInterface
     */
    public function set($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'inputFile',
            'outputDir'
        ));

        $resolver->setDefaults(array(
            'verbose'                        => false,
            'namespaceName'                  => '',
            'classNames'                     => '',
            'sharedTypes'                    => false,
            'constructorParamsDefaultToNull' => false,
            'soapClientClass'               => '\SoapClient',
            'soapClientOptions'             => array(),
            'proxy'                         => false
        ));

        $resolver->setNormalizers(array(
            'classNames' => function (Options $options, $value) {
                if (strlen($value) === 0) {
                    return array();
                }

                return array_map('trim', explode(',', $value));
            },

            'soapClientOptions' => function (Options $options, $value) {
                // The SOAP_SINGLE_ELEMENT_ARRAYS feature should be enabled by default if no other option has been set
                // explicitly while leaving this out. This cannot be handled in the defaults as soapClientOptions is a
                // nested array.
                if (!isset($value['features'])) {
                    $value['features'] = SOAP_SINGLE_ELEMENT_ARRAYS;
                }
                return $value;
            },

            'proxy' => function (Options $options, $value) {
                if (!$value) {
                    // proxy setting is optional
                    return false;
                }
                if (is_string($value)) {
                    $url_parts = parse_url($value);
                    $proxy_array = array(
                        'proxy_host' => $url_parts['host']
                    );
                    if (isset($url_parts['scheme'])) {
                        $proxy_array['proxy_scheme'] = $url_parts['scheme'];
                    }
                    if (isset($url_parts['port'])) {
                        $proxy_array['proxy_port'] = $url_parts['port'];
                    }
                    if (isset($url_parts['user'])) {
                        $proxy_array['proxy_login'] = $url_parts['user'];
                    }
                    if (isset($url_parts['pass'])) {
                        $proxy_array['proxy_password'] = $url_parts['pass'];
                    }
                    $value = $proxy_array;
                } elseif (is_array($value)) {
                    if (!array_key_exists('host', $value) && !array_key_exists('proxy_host', $value)) {
                        throw new InvalidArgumentException(
                            '"proxy" configuration setting must contain at least a key "host" or "proxy_host"'
                        );
                    }
                    // normalize short keys ('host') to SoapClient config standard ('proxy_host')
                    if (isset($value['host'])) {
                        $value['proxy_host'] = $value['host'];
                        unset($value['host']);
                    }
                    if (isset($value['port'])) {
                        $value['proxy_port'] = $value['port'];
                        unset($value['port']);
                    }
                    if (isset($value['login'])) {
                        $value['proxy_login'] = $value['login'];
                        unset($value['login']);
                    }
                    if (isset($value['password'])) {
                        $value['proxy_password'] = $value['password'];
                        unset($value['password']);
                    }
                } else {
                    throw new InvalidArgumentException(
                        '"proxy" configuration setting must be either a string containing the proxy url '
                        . 'or an array containing at least a key "host" or "proxy_host"'
                    );
                }
                $value['proxy_port'] = intval($value['proxy_port']); // make sure port is an integer
                $value['proxy_url'] = (isset($value['proxy_scheme']) ? $value['proxy_scheme'] . '://' : '');
                $value['proxy_url'] .= $value['proxy_host'] . ':' . $value['proxy_port'];
                if (isset($value['proxy_login']) && isset($value['proxy_password'])) {
                    $value['http_header_auth'] = array(
                        'Proxy-Authorization: Basic ' . base64_encode($value['proxy_login'] . ':' . $value['proxy_password'])
                    );
                }
                return $value;
            }
        ));
    }
}
