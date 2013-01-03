<?php

/*
 * This file is part of the GikoSinaweiboBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Giko\SinaweiboBundle\Security\Firewall;

use Giko\SinaweiboBundle\Security\Authentication\Token\SinaweiboAnywhereToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Giko\SinaweiboBundle\Security\Authentication\Token\SinaweiboUserToken;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\HttpFoundation\Request;

/**
 * Sinaweibo authentication listener.
 */
class SinaweiboListener extends AbstractAuthenticationListener
{
    private $useSinaweiboAnywhere = false;
    private $service;

    public function setUseSinaweiboAnywhere($bool)
    {
        $this->useSinaweiboAnywhere = (Boolean) $bool;
    }
    
    public function setService($service) {
        $this->service = $service;
    }

    protected function attemptAuthentication(Request $request)
    {
        if ($this->useSinaweiboAnywhere) {
            if (null === $identity = $request->cookies->get('sinaweibo_anywhere_identity')) {
                throw new AuthenticationException(sprintf('Identity cookie "sinaweibo_anywhere_identity" was not sent.'));
            }
            if (false === $pos = strpos($identity, ':')) {
                throw new AuthenticationException(sprintf('The submitted identity "%s" is invalid.', $identity));
            }

            return $this->authenticationManager->authenticate(SinaweiboAnywhereToken::createUnauthenticated(substr($identity, 0, $pos), substr($identity, $pos + 1)));
        }
        $info = $this->service->getAccessToken($request->query->get('code'));
        return $this->authenticationManager->authenticate(new SinaweiboUserToken($info['uid'], $info['access_token']));
    }
}
