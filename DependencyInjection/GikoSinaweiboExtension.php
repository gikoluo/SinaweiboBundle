<?php

/*
 * This file is part of the GikoSinaweiboBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Giko\SinaweiboBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Reference;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class GikoSinaweiboExtension extends Extension
{
    protected $resources = array(
        'sinaweibo' => 'sinaweibo.xml',
        'security' => 'security.xml',
    );

    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $this->loadDefaults($container);

        if (isset($config['alias'])) {
            $container->setAlias($config['alias'], 'giko_sinaweibo.service');
        }

        foreach (array('file', 'consumer_key', 'consumer_secret', 'callback_url', 'access_token', 'access_token_secret', 'anywhere_version') as $attribute) {
            if (isset($config[$attribute])) {
                $container->setParameter('giko_sinaweibo.'.$attribute, $config[$attribute]);
            }
        }
        require_once $config['file'];

        if (!empty($config['callback_route'])) {
            $container
                ->getDefinition('giko_sinaweibo.service')
                ->addMethodCall('setCallbackRoute', array(
                    new Reference('router'),
                    $config['callback_route'],
                ))
            ;
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getNamespace()
    {
        return 'http://friendsofsymfony.github.com/schema/dic/sinaweibo';
    }

    /**
     * @codeCoverageIgnore
     */
    protected function loadDefaults($container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(array(__DIR__.'/../Resources/config', __DIR__.'/Resources/config')));

        foreach ($this->resources as $resource) {
            $loader->load($resource);
        }
    }
}
