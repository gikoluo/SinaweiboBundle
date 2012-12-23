<?php

namespace Giko\SinaweiboBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;

interface UserManagerInterface extends UserProviderInterface
{
    /**
     * Creates a user for the given access token.
     *
     * @param array $token
     * @return UserInterface
     */
    function createUserFromAccessToken(array $token);
}