<?php
namespace stiks\eveonline_sso;

use Yii;
use yii\authclient\OAuth2;

class EveOnlineSSO extends OAuth2 {
    # enable state check
    public $validateAuthState = true;

    public $authUrl    = 'https://login.eveonline.com/oauth/authorize';
    public $tokenUrl   = 'https://login.eveonline.com/oauth/token';
    public $apiBaseUrl = 'https://login.eveonline.com/oauth';

    /**
     * Composes user authorization URL.
     * @param array $params additional auth GET params.
     * @return string authorization URL.
     */
    public function buildAuthUrl (array $params = []) {
        $defaultParams = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->getReturnUrl(),
            'xoauth_displayname' => Yii::$app->name,
        ];

        if (!empty ($this->scope)) {
            $defaultParams['scope'] = implode (' ', $this->scope);
        }

        if ($this->validateAuthState) {
            $authState = $this->generateAuthState ();
            $this->setState('authState', $authState);
            $defaultParams['state'] = $authState;
        }

        return $this->composeUrl ($this->authUrl, array_merge ($defaultParams, $params));
    }

    protected function defaultViewOptions () {
        return [
            'popupWidth' => 800,
            'popupHeight' => 800,
        ];
    }

    public function initUserAttributes () {
        return $this->api('verify', 'GET');
    }

    public function applyAccessTokenToRequest ($request, $accessToken) {
        $request->getHeaders()->add('Authorization', 'Bearer '.$accessToken->getToken ());
    }

    protected function defaultName () {
        return 'eveonline-sso';
    }

    protected function defaultTitle () {
        return 'EveOnline';
    }
}
?>
