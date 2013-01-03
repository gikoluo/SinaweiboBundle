Introduction
============


WARNING: DO NOT fork or install now, It hasn't finished yes.


This Bundle enables integration with Twitter PHP. Furthermore it
also provides a Symfony2 authentication provider so that users can login to a
Symfony2 application via Twitter. Furthermore via custom user provider support
the Twitter login can also be integrated with other data sources like the
database based solution provided by FOSUserBundle.

``If you are using Symfony 2.0 switch to the branch v1.0 of TwitterBundle or use the tag 1.0.0``

[![Build Status](https://secure.travis-ci.org/FriendsOfSymfony/FOSTwitterBundle.png)](http://travis-ci.org/FriendsOfSymfony/FOSTwitterBundle)

Installation
============

  1. Add this bundle and Abraham Williams' Twitter library to your project as Git submodules:

          $ git submodule add git://github.com/FriendsOfSymfony/FOSTwitterBundle.git vendor/bundles/FOS/TwitterBundle
          $ git submodule add git://github.com/kertz/twitteroauth.git vendor/twitteroauth

>**Note:** The kertz/twitteroauth is patched to be compatible with FOSTwitterBundle

  2. Register the namespace `Giko` to your project's autoloader bootstrap script:

          //app/autoload.php
          $loader->registerNamespaces(array(
                // ...
                'Giko'    => __DIR__.'/../vendor/bundles',
                // ...
          ));

  3. Add this bundle to your application's kernel:

          //app/AppKernel.php
          public function registerBundles()
          {
              return array(
                  // ...
                  new Giko\SinaweiboBundle\GikoSinaweiboBundle(),
                  // ...
              );
          }

  4. Configure the `sinaweibo` service in your YAML configuration:

            #app/config/config.yml
            giko_sinaweibo:
                file: %kernel.root_dir%/../vendor/sinalib/saetv2.ex.class.php
                consumer_key: xxxxxx
                consumer_secret: xxxxxx
                callback_url: http://localhost:8000/login_check

  5. Add the following configuration to use the security component:

            #app/config/security.yml
            security:
                providers:
                    giko_sinaweibo:
                        id: giko_sinaweibo.auth
                firewalls:
                    secured:
                        pattern:   /secured/.*
                        giko_sinaweibo: true
                    public:
                        pattern:   /.*
                        anonymous: true
                        giko_sinaweibo: true
                        logout: true
                access_control:
                    - { path: /.*, role: [IS_AUTHENTICATED_ANONYMOUSLY] }

Using Twitter @Anywhere
-----------------------

>**Note:** If you want the Security Component to work with Twitter @Anywhere, you need to send a request to the configured check path upon successful client authentication (see https://gist.github.com/1021384 for a sample configuration).

A templating helper is included for using Twitter @Anywhere. To use it, first
call the `->setup()` method toward the top of your DOM:

        <!-- inside a php template -->
          <?php echo $view['twitter_anywhere']->setup() ?>
        </head>

        <!-- inside a twig template -->
          {{ twitter_anywhere_setup() }}
        </head>

Once that's done, you can queue up JavaScript to be run once the library is
actually loaded:

        <!-- inside a php template -->
        <span id="twitter_connect"></span>
        <?php $view['twitter_anywhere']->setConfig('callbackURL', 'http://www.example.com/login_check') ?>
        <?php $view['twitter_anywhere']->queue('T("#twitter_connect").connectButton()') ?>

        <!-- inside a twig template -->
        <span id="twitter_connect"></span>
        {{ twitter_anywhere_setConfig('callbackURL', 'http://www.example.com/login_check') }}
        {{ twitter_anywhere_queue('T("#twitter_connect").connectButton()') }}

Finally, call the `->initialize()` method toward the bottom of the DOM:

        <!-- inside a php template -->
          <?php $view['twitter_anywhere']->initialize() ?>
        </body>

        <!-- inside a twig template -->
        {{ twitter_anywhere_initialize() }}
        </body>

### Configuring Twitter @Anywhere

You can set configuration using the templating helper. with the setConfig() method.


Example Custom User Provider using the FOSUserBundle
-------------------------------------------------------


To use this provider you will need to add a new service in your config.yml

``` yaml
# app/config/config.yml
services:
        my.twitter.user:
            class: Acme\YourBundle\Security\User\Provider\TwitterProvider
            arguments:
                twitter_oauth: "@giko_sinaweibo.api"
                userManager: "@fos_user.user_manager"
                validator: "@validator"
                session: "@session" 
```

Also you would need some new properties and methods in your User model class.

``` php
<?php
// src/Acme/YourBundle/Entity/User.php
    
    /**
     * @var string $sinaweiboId
     * 
     * @ORM\Column(name="sinaweibo_id", type="string", length=80, nullable=true)
     */
    private $sinaweiboId;
    
    /**
     * @var string $sinaweiboUsername
     * 
     * @ORM\Column(name="sinaweibo_username", type="string", length=100, nullable=true)
     */
    private $sinaweiboUsername;
    
    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }
    

    /**
     * Set sinaweiboId
     *
     * @param string $sinaweiboId
     * @return User
     */
    public function setSinaweiboId($sinaweiboId)
    {
        $this->sinaweiboId = $sinaweiboId;
        $this->setUsername($sinaweiboId);
        return $this;
    }

    /**
     * Get sinaweiboId
     *
     * @return string 
     */
    public function getSinaweiboId()
    {
        return $this->sinaweiboId;
    }

    /**
     * Set sinaweiboUsername
     *
     * @param string $sinaweiboUsername
     * @return User
     */
    public function setSinaweiboUsername($sinaweiboUsername)
    {
        $this->sinaweiboUsername = $sinaweiboUsername;
    
        return $this;
    }

    /**
     * Get sinaweiboUsername
     *
     * @return string 
     */
    public function getSinaweiboUsername()
    {
        return $this->sinaweiboUsername;
    }
        
```

Add this field to the doctrine xml:

``` xml
//Acme/YourBundle/Resources/config/doctrine/User.orm.xml
<entity name="Acme\YourBundle\Entity\User" table="fos_user_user">
  <id name="id" column="id" type="integer">
    <generator strategy="AUTO" />
  </id>
  <field name="sinaweiboId"    type="string"   column="sinaweibo_id" length="255"    nullable="true" />
  <field name="sinaweiboUsername"    type="string"   column="sinaweibo_username" length="255"    nullable="true" />
</entity>
```
>**Note:** You are forced to use the XML definition by fos and sonata user bundles. Anotations is not effective.


And this is the TwitterProvider class

``` php
<?php
// src/Acme/YourBundle/Security/User/Provider/TwitterProvider.php


namespace Acme\YourBundle\Security\User\Provider;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Session;
use \TwitterOAuth;
use FOS\UserBundle\Entity\UserManager;
use Symfony\Component\Validator\Validator;

class TwitterProvider implements UserProviderInterface
{
    /** 
     * @var \Twitter
     */
    protected $twitter_oauth;
    protected $userManager;
    protected $validator;
    protected $session;

    public function __construct(TwitterOAuth $twitter_oauth, UserManager $userManager,Validator $validator, Session $session)
    {   
        $this->twitter_oauth = $twitter_oauth;
        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->session = $session;
    }   

    public function supportsClass($class)
    {   
        return $this->userManager->supportsClass($class);
    }   

    public function findUserByTwitterId($twitterID)
    {   
        return $this->userManager->findUserBy(array('twitterID' => $twitterID));
    }   

    public function loadUserByUsername($username)
    {
        $user = $this->findUserByTwitterId($username);


         $this->twitter_oauth->setOAuthToken( $this->session->get('access_token') , $this->session->get('access_token_secret'));

        try {
             $info = $this->twitter_oauth->get('account/verify_credentials');
        } catch (Exception $e) {
             $info = null;
        }

        if (!empty($info)) {
            if (empty($user)) {
                $user = $this->userManager->createUser();
                $user->setEnabled(true);
                $user->setPassword('');
                $user->setAlgorithm('');
            }

            $username = $info->screen_name;


            $user->setTwitterID($info->id);
            $user->setTwitterUsername($username);
            $user->setEmail('');
            $user->setFirstname($info->name);

            $this->userManager->updateUser($user);
        }

        if (empty($user)) {
            throw new UsernameNotFoundException('The user is not authenticated on twitter');
        }

        return $user;

    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user)) || !$user->getTwitterID()) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getTwitterID());
    }
}
```


Finally, to get the authentication tokens from Twitter you would need to create an action in your controller like this one.

``` php

<?php
// src/Acme/YourBundle/Controller/DefaultController.php

        /** 
        * @Route("/connectTwitter", name="connect_twitter")
        *
        */
        public function connectTwitterAction()
        {   

          $request = $this->get('request');
          $twitter = $this->get('giko_sinaweibo.service');

          $authURL = $twitter->getLoginUrl($request);

          $response = new RedirectResponse($authURL);

          return $response;

        }  

```

You can create a button in your Twig template that will send the user to authenticate with Twitter.

```
         <a href="{{ path ('connect_twitter')}}"> <img src="/images/twitterLoginButton.png"></a> 

```

* Note: Your callback URL in your config.yml must point to your configured check_path

``` yaml
# app/config/config.yml

        giko_sinaweibo:
            ...
            callback_url: http://www.yoursite.com/twitter/login_check
```

Remember to edit your security.yml to use this provider


``` yaml
# app/config/security.yml

        security:
            encoders:
                Symfony\Component\Security\Core\User\User: plaintext

            role_hierarchy:
                ROLE_ADMIN:       ROLE_USER
                ROLE_SUPER_ADMIN: [ROLE_USER, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]

            providers:

                my_giko_sinaweibo_provider:
                    id: my.twitter.user 

            firewalls:
                dev:
                    pattern:  ^/(_(profiler|wdt)|css|images|js)/
                    security: false

                public:
                    pattern:  /
                    giko_sinaweibo:
                        login_path: /twitter/login
                        check_path: /twitter/login_check
                        default_target_path: /
                        provider: my_giko_sinaweibo_provider

                    anonymous: ~

```
