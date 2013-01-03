<?php

namespace Giko\SinaweiboBundle\Controller;
use FOS\RestBundle\Controller\FOSRestController;

use Sonata\MediaBundle\Security\PublicDownloadStrategy;

use FOS\Rest\Util\Codes;

use Symfony\Component\HttpKernel\Exception\HttpException;

use Symfony\Component\Process\Exception\RuntimeException;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use FOS\RestBundle\View\RouteRedirectView;

use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use FOS\UserBundle\Controller\SecurityController;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerBuilder;


class StatusesController extends FOSRestController {
    /**
     * @Route("/update_sinaweibo", name="connect_sinaweibo")
     * 
     */
    public function updateAction()
    {
        $request = $this->get('request');
        $status = $this->getRequest()->request->get('status');
        $lat = $this->getRequest()->request->get('lat', null);
        $lng = $this->getRequest()->request->get('lng', null);
        $annotations = [];
        
        $sinaweibo = $this->get('giko_sinaweibo.service');
        $data = $sinaweibo->getClient()->update( $status, $lat, $lng, $annotations);
        $serializer = SerializerBuilder::create()->build();
        $res = $serializer->serialize($data, 'json');
        
        return new Response($res);
    }
}
