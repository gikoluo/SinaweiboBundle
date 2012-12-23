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

use Giko\SinaweiboBundle\Security\Authentication\Token\TwitterAnywhereToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Giko\SinaweiboBundle\Security\Authentication\Token\TwitterUserToken;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\HttpFoundation\Request;

/**
 * Twitter authentication listener.
 */
class TwitterListener extends AbstractAuthenticationListener
{
    private $useTwitterAnywhere = false;

    public function setUseTwitterAnywhere($bool)
    {
        $this->useTwitterAnywhere = (Boolean) $bool;
    }

    protected function attemptAuthentication(Request $request)
    {
        if ($this->useTwitterAnywhere) {
            if (null === $identity = $request->cookies->get('twitter_anywhere_identity')) {
                throw new AuthenticationException(sprintf('Identity cookie "twitter_anywhere_identity" was not sent.'));
            }
            if (false === $pos = strpos($identity, ':')) {
                throw new AuthenticationException(sprintf('The submitted identity "%s" is invalid.', $identity));
            }

            return $this->authenticationManager->authenticate(TwitterAnywhereToken::createUnauthenticated(substr($identity, 0, $pos), substr($identity, $pos + 1)));
        }

        return $this->authenticationManager->authenticate(new TwitterUserToken($request->query->get('oauth_token'), $request->query->get('oauth_verifier')));
    }
}
