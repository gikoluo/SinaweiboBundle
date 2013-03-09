<?php

/*
 * This file is part of the GikoSinaweiboBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Giko\SinaweiboBundle;

use Giko\SinaweiboBundle\DependencyInjection\Security\Factory\SinaweiboFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GikoSinaweiboBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new SinaweiboFactory());
    }
}
