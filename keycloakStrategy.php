<?php
/**
 * keycloak strategy for Opauth
 * based on https://developers.keycloak.com/accounts/docs/OAuth2
 *
 * More information on Opauth: http://opauth.org
 *
 * @copyright    Copyright © 2012 U-Zyn Chua (http://uzyn.com)
 * @link         http://opauth.org
 * @package      Opauth.keycloakStrategy
 * @license      MIT License
 */

/**
 * keycloak strategy for Opauth
 * based on https://developers.keycloak.com/accounts/docs/OAuth2
 *
 * @package			Opauth.keycloak
 */
class keycloakStrategy extends OpauthStrategy
{
    /**
     * Compulsory config keys, listed as unassociative arrays
     */
    public $expects = array('client_id', 'client_secret');

    /**
     * Optional config keys, without predefining any default values.
     */
    public $optionals = array('auth_endpoint', 'token_endpoint', 'user_info_endpoint',
        'redirect_uri', 'scope', 'state', 'access_type', 'approval_prompt');

    /**
     * Optional config keys with respective default values, listed as associative arrays
     * eg. array('scope' => 'email');
     */
    public $defaults = array(
        'redirect_uri' => '{complete_url_to_strategy}oauth2callback',
        'scope' => 'profile email',
        'auth_endpoint' => 'https://dev.id.org.br/auth/realms/saude/protocol/openid-connect/auth',
        'token_endpoint' => 'https://dev.id.org.br/auth/realms/saude/protocol/openid-connect/token',
        'user_info_endpoint' => 'https://dev.id.org.br/auth/realms/saude/protocol/openid-connect/userinfo'
    );

    /**
     * Parameters that should not be sent to OAuth
     */
    public $configOnly = array(
        'auth_endpoint', 'token_endpoint', 'user_info_endpoint'
    );

    /**
     * Auth request
     */
    public function request()
    {
        $url    = $this->strategy['auth_endpoint'];
        $params = array(
            'client_id' => $this->strategy['client_id'],
            'redirect_uri' => $this->strategy['redirect_uri'],
            'response_type' => 'code',
            'scope' => $this->strategy['scope']
        );

        foreach ($this->optionals as $key) {
            if (!empty($this->strategy[$key]) && array_search($key,
                    $this->configOnly) === false) {
                $params[$key] = $this->strategy[$key];
            }
        }

        $this->clientGet($url, $params);
    }

    /**
     * Internal callback, after OAuth
     */
    public function oauth2callback()
    {
        if (array_key_exists('code', $_GET) && !empty($_GET['code'])) {
            $code     = $_GET['code'];
            $url      = $this->strategy['token_endpoint'];
            $params   = array(
                'code' => $code,
                'client_id' => $this->strategy['client_id'],
                'client_secret' => $this->strategy['client_secret'],
                'redirect_uri' => $this->strategy['redirect_uri'],
                'grant_type' => 'authorization_code'
            );
            //POST PARA KEYCLOAK COM OS DADOS E RECEBENDO A RESPOSTA
            $response = $this->serverPost($url, $params, null, $headers);
            
            $results = json_decode($response, true);
            
            if (!empty($results) && !empty($results['access_token'])) {
                //TOKEN EM FORMATO JWT
                $userinfo = $this->userinfo($results['access_token']);
                                
                $this->auth = array(
                    'uid' => $userinfo['sub'],//ID DO USUÁRIO NO KEYCLOAK
                    'info' => array(),
                    'credentials' => array(
                        'token' => $results['access_token'],
                        'expires' => date('c', time() + $results['expires_in'])
                    ),
                    'raw' => $userinfo
                );
                //
                if (!empty($result['refresh_token'])) {
                    $this->auth['credentials']['refresh_token'] = $result['refresh_token'];
                }
                //MAPEANDO O PERFIL COM O QUE VEM DE DADOS DO KEYCLOAK
                $this->mapProfile($userinfo, 'name', 'name');
                $this->mapProfile($userinfo, 'email', 'email');
                $this->mapProfile($userinfo, 'given_name', 'first_name');
                $this->mapProfile($userinfo, 'family_name', 'surname');
                $this->mapProfile($userinfo, 'picture', 'profile_picture_url');

                $this->callback();
            } else {
                $error = array(
                    'code' => 'access_token_error',
                    'message' => 'Failed when attempting to obtain access token',
                    'raw' => array(
                        'response' => $response,
                        'headers' => $headers
                    )
                );

                $this->errorCallback($error);
            }
        } else {
            $error = array(
                'code' => 'oauth2callback_error',
                'raw' => $_GET
            );

            $this->errorCallback($error);
        }
        
    }

    /**
     * Queries keycloak API for user info
     *
     * @param string $access_token
     * @return array Parsed JSON results
     */
    private function userinfo($access_token)
    {
        //RECEBENDO O TOKEN E PASSANDO PARA SER DECODIFICADO
        $userinfo = JWT::decode(
            $access_token, 
            null, 
            array('RS256')
        );
        
        if (!empty($userinfo)) {
            $encUserInfo = json_encode($userinfo);
            return $this->recursiveGetObjectVars(json_decode($encUserInfo));
        } else {
            $error = array(
                'code' => 'userinfo_error',
                'message' => 'Failed when attempting to query for user information',
                'raw' => array(
                    'response' => $userinfo,
                    'headers' => $headers
                )
            );

            $this->errorCallback($error);
        }
    }
}
