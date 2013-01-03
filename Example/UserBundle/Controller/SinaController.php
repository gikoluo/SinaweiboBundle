<?php

namespace Acme\UserBundle\Controller;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class SinaController extends Controller {
    /**
     * @Route("/sinaweibo/login", name="connect_sinaweibo")
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
     * @Route("/sinaweibo/login_check", name="logincheck_sinaweibo")
     */
    public function logincheckSinaweiboAction()
    {
        echo "";
    }
    
    /**
     * @Route("/sinaweibo/callback", name="callback_sinaweibo")
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
        
        //your logic here
        $resp = var_export($data, true);
        return new Response('<pre>' . $resp . '</pre>');
    }
    
    /**
     * @Route("/sinaweibo/update", name="update_sinaweibo")
     *
     */
    public function updateAction()
    {
        $request = $this->get('request');
        $status = $this->getRequest()->request->get('status');
        $lat = $this->getRequest()->request->get('lat', null);
        $lng = $this->getRequest()->request->get('lng', null);
        $annotations = array();
    
        $sinaweibo = $this->get('giko_sinaweibo.service');
        $data = $sinaweibo->getClient()->update( $status, $lat, $lng, $annotations);
        
        //your logic here
        $resp = var_export($data, true);
        return new Response('<pre>' . $resp . '</pre>');
    }
}
