<?php

/*
 * This file is part of the GikoSinaweiboBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Giko\SinaweiboBundle\Tests\DependencyInjection;

use Giko\SinaweiboBundle\DependencyInjection\GikoSinaweiboExtension;

class TwitterExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Bundle\Giko\SinaweiboBundle\DependencyInjection\GikoSinaweiboExtension::load
     */
    public function testLoadFailed()
    {
        $this->setExpectedException('\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

        $container = $this->getMock('Symfony\\Component\\DependencyInjection\\ContainerBuilder');

        $extension = new GikoSinaweiboExtension();
        $extension->load(array(), $container);
    }

    /**
     * @covers Bundle\Giko\SinaweiboBundle\DependencyInjection\GikoSinaweiboExtension::load
     */
    public function testLoadSuccess()
    {
        $configs = array(
            array(
                'file' => 'foo',
                'callback_url' => 'foo',
                'consumer_key' => 'foo',
                'consumer_secret' => 'foo',
            ),
        );

        $container = $this->getMock('Symfony\\Component\\DependencyInjection\\ContainerBuilder');
        $parameterbag = $this->getMock('Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface');
        $container
            ->expects($this->once())
            ->method('getParameterBag')
            ->with()
            ->will($this->returnValue($parameterbag));

        $alias = 'bar';

        $container
            ->expects($this->once())
            ->method('setAlias')
            ->with($alias, 'fos_twitter');

        $configs[] = array('alias' => $alias);

        $extension = new GikoSinaweiboExtension();
        $extension->load($configs, $container);
    }
}
