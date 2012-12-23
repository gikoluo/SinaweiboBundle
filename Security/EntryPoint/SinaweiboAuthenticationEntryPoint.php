<?php

/*
 * This file is part of the GikoSinaweiboBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Giko\SinaweiboBundle\Security\EntryPoint;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContext;

use Giko\SinaweiboBundle\Security\Exception\ConnectionException;
use Giko\SinaweiboBundle\Services\Sinaweibo;

/**
 * SinaweiboAuthenticationEntryPoint starts an authentication via Sinaweibo.
 */
class SinaweiboAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    protected $sinaweibo;

    /**
     * Constructor
     *
     * @param Sinaweibo $sinaweibo
     */
    public function __construct(Sinaweibo $sinaweibo)
    {
        $this->sinaweibo = $sinaweibo;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $authURL = $this->sinaweibo->getLoginUrl();
        if (!$authURL) {
            throw new ConnectionException('Could not connect to Sinaweibo!');
        }

        return new RedirectResponse($authURL);
    }
}
