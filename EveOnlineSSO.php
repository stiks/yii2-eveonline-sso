<?php
namespace stiks\eveonline_sso;

use yii\authclient\OAuth2;

class EveOnlineSSO extends OAuth2 {
    public $id = 'eve-online-sso';

    public $validateAuthState = true;

    public $authUrl = 'https://login.eveonline.com/oauth/authorize';
    public $tokenUrl = 'https://login.eveonline.com/oauth/token';
    public $apiBaseUrl = 'https://login.eveonline.com/oauth';

    public function initUserAttributes() {
        return $this->api('verify', 'GET');
    }

    public function applyAccessTokenToRequest($request, $accessToken) {
        $request->getHeaders()->add('Authorization', 'Bearer '.$accessToken->getToken());
    }

    protected function defaultName() {
        return 'eveonline-sso';
    }

    protected function defaultTitle() {
        return 'EveOnline SSO';
    }
}
?>
