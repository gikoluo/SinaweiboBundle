介绍
============

本组件可将新浪微博集成到Symfony2中。 组件提供新浪微博登陆认证，并可利用新浪微博接口进行微博发布等分享行为。
本组件整合FOSUserBundle，保存新浪微博登陆后的用户信息

``组件仅支持Symfony2.1+``


## 安装
============

### Step 1. 将本组件 ```giko/sinaweibo-bundle``` 和 ```friendsofsymfony/user-bundle```  添加到 ``composer.json`` 文件:
```
        "repositories": [
            {
                "type": "vcs",
                "url":  "https://github.com/gikoluo/SinaweiboBundle.git"
            }
        ],
        "require": {
            #...
            "friendsofsymfony/user-bundle": "dev-master",
            "giko/sinaweibo-bundle": "dev-master",
        }
```

### Step 2. 使用Git submodules的方式将 ElmerZhang / WeiboSDK 新浪微博代码添加代码库。 或者你也可以通过手动下载的方式下载并解压到对应的目录。
此步骤现在省略吧。  ```giko/sinaweibo-bundle``` 中已经自带了一个WeiboSDK copy，而且修改了几行代码来解决一个notiec错误。。
```
          $ git submodule add git://github.com/ElmerZhang/WeiboSDK.git vendor/sinalib
```

### Step 3. 在应用内核代码中注册组件：
```php
          //app/AppKernel.php
          public function registerBundles()
          {
              return array(
                  // ...
                  new FOS\UserBundle\FOSUserBundle(),
                  new Giko\SinaweiboBundle\GikoSinaweiboBundle(),
                  // ...
              );
          }
```

### Step 4. 配置FOS User。 
> Note: 关于FOS User的更多信息，请参考 https://github.com/FriendsOfSymfony/FOSUserBundle
        
```
	#app/config/config.yml
	#FOS User
	fos_user:
	    db_driver:      orm # can be orm or odm
	    firewall_name:  main
	    user_class:     Acme\UserBundle\Entity\User
	    use_listener:           true
	    use_username_form_type: true
	    service:
	        mailer:                 fos_user.mailer.default
	        email_canonicalizer:    fos_user.util.canonicalizer.default
	        username_canonicalizer: fos_user.util.canonicalizer.default
	        token_generator:        fos_user.util.token_generator.default
	        user_manager:           fos_user.user_manager.default
	    group:
	        group_class: Acme\UserBundle\Entity\Group
	    profile:
	        form:
	            type:               fos_user_profile
	            name:               fos_user_profile_form
	            validation_groups:  [Profile, Default]
```

### Step 5. 配置`新浪微博`组件:
``` yaml
	#app/config/config.yml
	giko_sinaweibo:
	    file: %kernel.root_dir%/../vendor/sinalib/saetv2.ex.class.php
	    consumer_key: xxxxxx
	    consumer_secret: xxxxxx
	    callback_url: http://localhost:8000/login_check
```

### Step 6. 使用FOSUserBundle建立你自己的用户模块
  建立用户Model，并增加几个新浪微博字段：
``` php
	<?php
	// src/Acme/UserBundle/Entity/User.php
	
	namespace Acme\UserBundle\Entity;
	
	use FOS\UserBundle\Entity\User as BaseUser;
	use Doctrine\ORM\Mapping as ORM;
	
	/**
	 * @ORM\Entity
	 * @ORM\Table(name="fos_user")
	 */
	class User extends BaseUser {
	  /**
		 * @ORM\Id
		 * @ORM\Column(type="integer")
		 * @ORM\GeneratedValue(strategy="AUTO")
		 */
		protected $id;
	
		public function __construct() {
			parent::__construct();
			// your own logic
		}
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
		public function getId() {
			return $this->id;
		}
	
		/**
		 * Set sinaweiboId
		 *
		 * @param string $sinaweiboId
		 * @return User
		 */
		public function setSinaweiboId($sinaweiboId) {
			$this->sinaweiboId = $sinaweiboId;
			$this->setUsername($sinaweiboId);
			return $this;
		}
	
		/**
		 * Get sinaweiboId
		 *
		 * @return string 
		 */
		public function getSinaweiboId() {
			return $this->sinaweiboId;
		}
	
		/**
		 * Set sinaweiboUsername
		 *
		 * @param string $sinaweiboUsername
		 * @return User
		 */
		public function setSinaweiboUsername($sinaweiboUsername) {
			$this->sinaweiboUsername = $sinaweiboUsername;
	
			return $this;
		}
	
		/**
		 * Get sinaweiboUsername
		 *
		 * @return string 
		 */
		public function getSinaweiboUsername() {
			return $this->sinaweiboUsername;
		}
	}
```
  
*> Note: config.yml中的```callback_url```必须与新浪微博接口中回调地址设置一致。

### Step 7. 建立新浪微博Controller：
``` php
	<?php
    namespace Acme\UserBundle\Controller;
    
    use Symfony\Component\HttpFoundation\RedirectResponse;
    class SinaController extends Controller {
        /**
         * @Route("/connect_sinaweibo", name="connect_sinaweibo")
         * 
         */
        public function sinaAction()
        {
            $request = $this->get('request');
            $sinaweibo = $this->get('giko_sinaweibo.service');
            $authURL = $sinaweibo->getLoginUrl($request);
            $response = new RedirectResponse($authURL);
            return $response;
        }
        
        /**
         * @Route("/callback_sinaweibo", name="callback_sinaweibo")
         *
         */
        public function callbackSinaweiboAction()
        {
            /**
             * @return Response
             *
             * @throws AccessDeniedException
             */
            $user = $this->getUser();
            $sinaweibo = $this->get('giko_sinaweibo.service');
            $sinaInfo = $sinaweibo->getClient()->show_user_by_id($user->getSinaweiboId());
            $data = array('user'=>$user, 'weiboInfo' => $user);
            
            var_dump($data);
            //your login here
            exit;
        }
    }
```

增加Route
```
    user_login:
        resource: "@AcmeUserBundle/Controller/"
        type:     annotation
        prefix:   /
```

### Step 8. 在安全配置中，增加以下设置:
``` yaml
	#app/config/security.yml
	security:
	    providers:
	        chain_provider:
	            chain:
	                providers: [fos_userbundle, wodula_giko_sinaweibo_provider]
	        fos_userbundle:
	            id: fos_user.user_provider.username
	        wodula_giko_sinaweibo_provider:
	            id: wodula.sinaweibo.user
	    firewalls:
	        public:
	            pattern:  /
	            giko_sinaweibo:
	              login_path: /sinaweibo/login
	              check_path: /sinaweibo/login_check
	              default_target_path: /sinaweibo/callback
	              provider: wodula_giko_sinaweibo_provider
	            logout: true
	            anonymous: true
	    access_control:
	       - { path: ^/sinaweibo.*, role: ROLE_USER }
```

### Step 9. 在模板文件中，放置新浪微博的登陆按钮

```
         <a href="{{ path ('connect_sinaweibo')}}"> <img src="/images/sinaweiboLoginButton.png"></a> 
```
### Step 10.  好吧
 好吧，我承认，上面的流程太长了点。我不该这么折磨你。其实在Example中，有现成的代码。按照需要，提取到你的代码中即可。


使用 新浪微博小组件 @JS-Widget
-----------------------

组件已包含了用户@JS-Widget的模板插件，使用前，需要在你的模板文件的顶部中进行注册：

        <!-- inside a php template -->
          <?php echo $view['sinaweibo_anywhere']->setup() ?>
        </head>

        <!-- inside a twig template -->
          {{ sinaweibo_anywhere_setup() }}
        </head>

注册好了之后，在你需要放置按钮的地方，写这么一段JS代码：
        <!-- inside a php template -->
        <span id="sinaweibo_connect"></span>
        <?php $view['sinaweibo_anywhere']->setConfig('callbackURL', 'http://www.example.com/login_check') ?>
        <?php $view['sinaweibo_anywhere']->queue('T("#sinaweibo_connect").connectButton()') ?>

        <!-- inside a twig template -->
        <span id="sinaweibo_connect"></span>
        {{ sinaweibo_anywhere_setConfig('callbackURL', 'http://www.example.com/login_check') }}
        {{ sinaweibo_anywhere_queue('T("#sinaweibo_connect").connectButton()') }}

最后，调用`->initialize()`方法来完成所有的工作：
        <!-- inside a php template -->
          <?php $view['sinaweibo_anywhere']->initialize() ?>
        </body>

        <!-- inside a twig template -->
        {{ sinaweibo_anywhere_initialize() }}
        </body>

