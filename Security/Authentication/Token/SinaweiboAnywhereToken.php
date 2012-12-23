<?php

/*
 * This file is part of the GikoSinaweiboBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Giko\SinaweiboBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class SinaweiboAnywhereToken extends AbstractToken
{
    private $signature;

    /**
     * Constructor.
     *
     * Do not call this directly, use the static methods instead.
     *
     * @param mixed $user
     * @param array $roles
     */
    public function __construct($user, array $roles)
    {
        parent::__construct($roles);
        $this->setUser($user);
    }

    public static function createUnauthenticated($userId, $signature)
    {
        $token = new self($userId, array());
        $token->signature = $signature;

        return $token;
    }

    public static function createAuthenticated($user, array $roles)
    {
        $token = new self($user, $roles);
        $token->setAuthenticated(true);

        return $token;
    }

    public function getSignature()
    {
        return $this->signature;
    }

    public function getCredentials()
    {
        return '';
    }

    public function serialize()
    {
        return serialize(array($this->signature, parent::serialize()));
    }

    public function unserialize($str)
    {
        list($this->signature, $parentStr) = unserialize($str);
        parent::unserialize($parentStr);
    }
}
