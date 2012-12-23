<?php

/*
 * This file is part of the GikoSinaweiboBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Giko\SinaweiboBundle\Security\Authentication\Provider;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Giko\SinaweiboBundle\Security\Authentication\Token\TwitterAnywhereToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;

class TwitterAnywhereProvider implements AuthenticationProviderInterface
{
    private $consumerSecret;
    private $provider;
    private $checker;

    public function __construct($consumerSecret, UserProviderInterface $provider = null, UserCheckerInterface $checker = null)
    {
        $this->consumerSecret = $consumerSecret;
        $this->provider = $provider;
        $this->checker = $checker;
    }

    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        // previously authenticated user
        $user = $token->getUser();
        if ($user instanceof UserInterface) {
            if (null !== $this->checker) {
                $this->checker->checkPostAuth($user);
            }

            $authenticated = TwitterAnywhereToken::createAuthenticated($user, $user->getRoles());
            $authenticated->setAttributes($token->getAttributes());

            return $authenticated;
        }

        if (!$this->isSignatureValid($token->getSignature(), sha1($token->getUser().$this->consumerSecret))) {
            throw new AuthenticationException(sprintf('The presented signature was invalid.'));
        }

        if (null === $this->provider) {
            $authenticated = TwitterAnywhereToken::createAuthenticated($token->getUser(), array());
            $authenticated->setAttributes($token->getAttributes());

            return $authenticated;
        }

        try {
            $user = $this->provider->loadUserByUsername($token->getUser());
            $this->checker->checkPostAuth($user);

            $authenticated = TwitterAnywhereToken::createAuthenticated($user, $user->getRoles());
            $authenticated->setAttributes($token->getAttributes());

            return $authenticated;
        } catch (AuthenticationException $passthroughEx) {
            throw $passthroughEx;
        } catch (\Exception $ex) {
            throw new AuthenticationException($ex->getMessage(), null, 0, $ex);
        }
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof TwitterAnywhereToken;
    }

    private function isSignatureValid($actual, $expected)
    {
        if (strlen($actual) !== $c = strlen($expected)) {
            return false;
        }

        $result = 0;
        for ($i = 0; $i < $c; $i++) {
            $result |= ord($actual[$i]) ^ ord($expected[$i]);
        }

        return 0 === $result;
    }
}
