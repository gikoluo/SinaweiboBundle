<?php


namespace Giko\SinaweiboBundle\Security\User\Provider;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use FOS\UserBundle\Doctrine\UserManager;
use Symfony\Component\Validator\Validator;
use Giko\SinaweiboBundle\Services\SinaweiboOAuth;
use Assetic\Exception\Exception;

class SinaweiboProvider implements UserProviderInterface
{
    /** 
     * @var SinaweiboOAuth
     */
    protected $sinaweibo_oauth;
    protected $userManager;
    protected $validator;
    protected $session;

    public function __construct(SinaweiboOAuth $sinaweibo_oauth, UserManager $userManager,Validator $validator, Session $session)
    {
        $this->sinaweibo_oauth = $sinaweibo_oauth;
        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->session = $session;
    }

    public function supportsClass($class)
    {   
        return $this->userManager->supportsClass($class);
    }   

    public function findUserBySinaweiboId($sinaweiboId)
    {
        return $this->userManager->findUserBy(array('sinaweiboId' => $sinaweiboId));
    }   

    public function loadUserByUsername($userid)
    {
        $user = $this->findUserBySinaweiboId($userid);
        $accessToken = $this->session->get('access_token');
        $this->sinaweibo_oauth->setOAuthToken( $accessToken , $this->session->get('access_token_secret'));
        
        try {
             $info = $this->sinaweibo_oauth->show_user_by_id($userid);
        } catch (Exception $e) {
             $info = null;
        }
        if (!empty($info)) {
            $username = $info['screen_name'];
            
            if (empty($user)) {
                $user = $this->userManager->createUser();
                $user->setEnabled(true);
                $user->setPassword('');
            }
            $user->setSinaweiboId($info['id']);
            $user->setSinaweiboUsername($username);
            $user->setSinaweiboAccessToken($accessToken);
            $user->setEmail('');
            $rs = $this->userManager->updateUser($user);
        }
        if (empty($user)) {
            throw new UsernameNotFoundException('The user is not authenticated on sinaweibo');
        }
        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user)) || !$user->getSinaweiboID()) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getSinaweiboID());
    }
}