<?php
/**
 * Created by Amal Raghav <amal.raghav@gmail.com>
 * Date: 05/03/11
 */

namespace Giko\SinaweiboBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
    
class SinaweiboAnywhereExtension extends \Twig_Extension
{
    protected $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            'sinaweibo_anywhere_setup' => new \Twig_Function_Method($this, 'renderSetup', array('is_safe' => array('html'))),
            'sinaweibo_anywhere_initialize' => new \Twig_Function_Method($this, 'renderInitialize', array('is_safe' => array('html'))),
            'sinaweibo_anywhere_queue' => new \Twig_Function_Method($this, 'queue', array('is_safe' => array('html'))),
            'sinaweibo_anywhere_setConfig' => new \Twig_Function_Method($this, 'setConfig', array('is_safe' => array('html'))),
        );
    }

    public function renderSetup($parameters = array(), $name = null)
    {
        return $this->container->get('giko_sinaweibo.anywhere.helper')->setup($parameters, $name ?: 'GikoSinaweiboBundle::setup.html.twig');
    }

    public function renderInitialize($parameters = array(), $name = null)
    {
        return $this->container->get('giko_sinaweibo.anywhere.helper')->initialize($parameters, $name ?: 'GikoSinaweiboBundle::initialize.html.twig');
    }

     /*
     *
     */
    public function queue($script)
    {
        return $this->container->get('giko_sinaweibo.anywhere.helper')->queue($script);
    }

    /*
     *
     */
    public function setConfig($key, $value)
    {
        return $this->container->get('giko_sinaweibo.anywhere.helper')->setConfig($key, $value);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sinaweibo_anywhere';
    }
}
