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