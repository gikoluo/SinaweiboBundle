<?php
namespace Giko\SinaweiboBundle\Services;

class SinaweiboOAuth extends \SaeTClientV2 {
    public function setOAuthToken($oauthToken, $oauth_token_secret) {
        $this->access_token = $oauthToken;
    }
}
