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

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Giko\SinaweiboBundle\Security\User\UserManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\DependencyInjection\Container;

use Giko\SinaweiboBundle\Security\Authentication\Token\SinaweiboUserToken;
use Giko\SinaweiboBundle\Services\Sinaweibo;

class SinaweiboProvider implements AuthenticationProviderInterface
{
    private $sinaweibo;
    private $userProvider;
    private $userChecker;
    private $createUserIfNotExists;

    public function __construct(Sinaweibo $sinaweibo, UserProviderInterface $userProvider = null, UserCheckerInterface $userChecker = null, $createUserIfNotExists = false)
    {
        if (null !== $userProvider && null === $userChecker) {
            throw new \InvalidArgumentException('$userChecker cannot be null, if $userProvider is not null.');
        }

        if ($createUserIfNotExists && !$userProvider instanceof UserManagerInterface) {
            throw new \InvalidArgumentException('$userProvider must be an instanceof UserManagerInterface if createUserIfNotExists is true.');
        }

        $this->sinaweibo = $sinaweibo;
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
        $this->createUserIfNotExists = $createUserIfNotExists;
    }

    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }
        $user = $token->getUser();
        
        if ($user instanceof UserInterface) {
            // FIXME: Should we make a call to Sinaweibo for verification?
            $newToken = new SinaweiboUserToken($user, null, $user->getRoles());
            $newToken->setAttributes($token->getAttributes());
            
            return $newToken;
        }
        
        $this->sinaweibo->setToken($token->getOauthVerifier());
        
        try {
            if ($userInfo = $this->sinaweibo->getClient()->show_user_by_id($user)) { //TODO Maybe fail
                $newToken = $this->createAuthenticatedToken($userInfo);
                $newToken->setAttributes($token->getAttributes());
                return $newToken;
            }
            
        } catch (AuthenticationException $failed) {
            throw $failed;
        } catch (\Exception $failed) {
            throw new AuthenticationException($failed->getMessage(), null, $failed->getCode(), $failed);
        }
        throw new AuthenticationException('The Sinaweibo user could not be retrieved from the session.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof SinaweiboUserToken;
    }

    private function createAuthenticatedToken(array $accessToken)
    {
        if (null === $this->userProvider) {
            return new SinaweiboUserToken($accessToken['screen_name'], null, array('ROLE_TWITTER_USER'));
        }
        
        try {
            $user = $this->userProvider->loadUserByUsername($accessToken['id']);
            $this->userChecker->checkPostAuth($user);
        } catch (UsernameNotFoundException $ex) {
            if (!$this->createUserIfNotExists) {
                throw $ex;
            }
            $user = $this->userProvider->createUserFromAccessToken($accessToken);
        }
        
        if (!$user instanceof UserInterface) {
            throw new \RuntimeException('User provider did not return an implementation of user interface.');
        }

        return new SinaweiboUserToken($user, null, $user->getRoles());
    }
}
