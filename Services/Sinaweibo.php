<?php

/*
 * This file is part of the GikoSinaweiboBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Giko\SinaweiboBundle\Services;

use Symfony\Component\Routing\RouterInterface;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class Sinaweibo
{
    private $sinaweibo;
    private $session;
    private $router;
    private $callbackRoute;
    private $callbackURL;
    /**
     * 
     * @var Giko\SinaweiboBundle\Services\SinaweiboOAuth
     */
    private $client;

    public function __construct(SinaweiboOAuth $sinaweibo, Session $session, $callbackURL = null)
    {
        $this->sinaweibo = $sinaweibo;
        $this->session = $session;
        $this->callbackURL = $callbackURL;
        $this->client = new \SaeTClientV2($sinaweibo->oauth->client_id, $sinaweibo->oauth->client_secret, $this->session->get('oauth_token'));
    }
    
    public function getClient() {
        return $this->client;
    }
    
    public function setToken($token) {
        $this->session->set('oauth_token', $token);
        $this->client->oauth->access_token = $token;
        $this->sinaweibo->oauth->access_token = $token;
    }

    public function setCallbackRoute(RouterInterface $router, $routeName)
    {
        $this->router = $router;
        $this->callbackRoute = $routeName;
    }

    public function getLoginUrl()
    {
        /* Get temporary credentials. */
        $callbackUrl = $this->getCallbackUrl();
        $redirectURL = $this->sinaweibo->oauth->getAuthorizeURL($callbackUrl);
        return $redirectURL;
    }
    
    public function getAccessToken($code)
    {
        /* Request access tokens from sinaweibo */
        $accessToken = $this->sinaweibo->oauth->getAccessToken('code', 
                            array(
                                    'code' => $code,
                                    'redirect_uri' => $this->getCallbackUrl()
                            )
                );
        /* Save the access tokens. Normally these would be saved in a database for future use. */
        $this->session->set('access_token', $accessToken['access_token']);
        //$this->session->set('access_token_secret', $accessToken['oauth_token_secret']);

        /* Remove no longer needed request tokens */
        !$this->session->has('oauth_token') ?: $this->session->remove('oauth_token', null);
        !$this->session->has('oauth_token_secret') ?: $this->session->remove('oauth_token_secret', null);

        /* If HTTP response is 200 continue otherwise send to connect page to retry */
        if (200 == $this->sinaweibo->oauth->http_code) {
            /* The user has been verified and the access tokens can be saved for future use */
            return $accessToken;
        }

        /* Return null for failure */
        return null;
    }

    private function getCallbackUrl()
    {
        if (!empty($this->callbackURL)) {
            return $this->callbackURL;
        }

        if (!empty($this->callbackRoute)) {
            return $this->router->generate($this->callbackRoute, array(), true);
        }

        return null;
    }
}
