<?php

namespace Wsdl2PhpGenerator;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Wsdl2PhpGenerator\ConfigInterface;

/**
 * This class contains configurable key/value pairs.
 *
 * @package Wsdl2PhpGenerator
 * @author Jim Schmid <js@1up.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Config implements ConfigInterface
{
    /**
     * @var array The actual key/value pairs.
     */
    protected $options;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    public function get($key)
    {
        if (!array_key_exists($key, $this->options)) {
            throw new \InvalidArgumentException(sprintf('The key %s does not exist.', $key));
        }

        return $this->options[$key];
    }

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
            'oneFile'                        => false,
            'classExists'                    => false,
            'noTypeConstructor'              => false,
            'namespaceName'                  => '',
            'optionsFeatures'                => array(),
            'wsdlCache'                      => '',
            'compression'                    => '',
            'classNames'                     => '',
            'prefix'                         => '',
            'suffix'                         => '',
            'sharedTypes'                    => false,
            'createAccessors'                => false,
            'constructorParamsDefaultToNull' => false,
            'noIncludes'                     => false
        ));

        $resolver->setAllowedValues(array(
            'wsdlCache' => array('', 'WSDL_CACHE_NONE', 'WSDL_CACHE_DISK', 'WSDL_CACHE_MEMORY', 'WSDL_CACHE_BOTH')
        ));

        $resolver->setNormalizers(array(
            'classNames' => function(Options $options, $value) {
                if (strlen($value) === 0) {
                    return array();
                }

                return array_map('trim', explode(',', $value));
            }
        ));
    }
}
