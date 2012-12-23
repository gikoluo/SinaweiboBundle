<?php

/*
 * This file is part of the GikoSinaweiboBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Giko\SinaweiboBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;

class SinaweiboFactory extends AbstractFactory
{
    public function __construct()
    {
        $this->addOption('use_sinaweibo_anywhere', false);
        $this->addOption('create_user_if_not_exists', false);
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'giko_sinaweibo';
    }

    protected function getListenerId()
    {
        return 'giko_sinaweibo.security.authentication.listener';
    }

    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        // configure auth with sinaweibo Anywhere
        if (true === $config['use_sinaweibo_anywhere']) {
            if (isset($config['provider'])) {
                $authProviderId = 'giko_sinaweibo.anywhere_auth.'.$id;

                $container
                    ->setDefinition($authProviderId, new DefinitionDecorator('giko_sinaweibo.anywhere_auth'))
                    ->addArgument(new Reference($userProviderId))
                    ->addArgument(new Reference('security.user_checker'))
                    ->addArgument($config['create_user_if_not_exists'])
                ;

                return $authProviderId;
            }

            // no user provider
            return 'giko_sinaweibo.anywhere_auth';
        }

        // configure auth for standard sinaweibo API
        // with user provider
        if (isset($config['provider'])) {
            $authProviderId = 'giko_sinaweibo.auth.'.$id;

            $container
                ->setDefinition($authProviderId, new DefinitionDecorator('giko_sinaweibo.auth'))
                ->addArgument(new Reference($userProviderId))
                ->addArgument(new Reference('security.user_checker'))
                ->addArgument($config['create_user_if_not_exists'])
            ;

            return $authProviderId;
        }

        // without user provider
        return 'giko_sinaweibo.auth';
    }

    protected function createListener($container, $id, $config, $userProvider)
    {
        $listenerId = parent::createListener($container, $id, $config, $userProvider);

        if ($config['use_sinaweibo_anywhere']) {
            $container
                ->getDefinition($listenerId)
                ->addMethodCall('setUsesinaweiboAnywhere', array(true))
            ;
        }

        return $listenerId;
    }

    protected function createEntryPoint($container, $id, $config, $defaultEntryPointId)
    {
        $entryPointId = 'giko_sinaweibo.security.authentication.entry_point.'.$id;
        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('giko_sinaweibo.security.authentication.entry_point'))
        ;

        return $entryPointId;
    }
}
