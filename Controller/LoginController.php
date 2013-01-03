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


class LoginController extends FOSRestController {
    /**
     * @Route("/connect_sinaweibo", name="connect_sinaweibo")
     * 
     */
    public function sinaAction()
    {
        $request = $this->get('request');
        $sinaweibo = $this->get('giko_sinaweibo.service');
        $authURL = $sinaweibo->getLoginUrl($request);
        $response = new RedirectResponse($authURL);
        return $response;
    }
    
    /**
     * @Route("/connect_sinaweibo", name="connect_sinaweibo")
     *
     */
    public function callbackSinaweiboAction()
    {
        /**
         * @return Response
         *
         * @throws AccessDeniedException
         */
        $user = $this->getUser();
        $sinaweibo = $this->get('giko_sinaweibo.service');
        $sinaInfo = $sinaweibo->getClient()->show_user_by_id($user->getSinaweiboId());
        $data = array('user'=>$user, 'weiboInfo' => $user);
        $serializer = SerializerBuilder::create()->build();
        $res = $serializer->serialize($data, 'json');
        
        
        return new Response($res);
    }
}
