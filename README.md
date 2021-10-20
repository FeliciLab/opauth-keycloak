Getting started
----------------	----------------
1. Install Opauth-Keycloak:
   In applications for using cultural maps
   ```bash
   cd src/protected
   ```

   composer.json add 
      ```
      "opauth/keycloak" : "*",
      ```
   In "repositories"
   ```
   https://github.com/EscolaDeSaudePublica/opauth-keycloak
   ```
   Or access src/protected/vendor/opauth
   ```
   git clone https://github.com/EscolaDeSaudePublica/opauth-keycloak keycloak
   ```

2. Create a KeyCloak APIs project at https://code.loginCidadao.com/apis/console/
   - Registgra em App.php $this->registerAuthProvider('keycloak'); or add the register() method within your theme and write the code in your theme - (file Theme.php)
   ```
       function register() {
        parent::register();

        $app = App::i();
        $app->registerAuthProvider('keycloak');
    }
    ```
   - Rove the OpauthKeyCloak.php file to the protected/application/lib/Mapasculturais/AuthProvaiders path
   - You do not have to enable any services from the Services tab
   - Make sure to go to **API Access** tab and **Create an OAuth 2.0 client ID**.
   - Choose **Web application** for *Application type*
   - Make sure that redirect URI is set to actual OAuth 2.0 callback URL, usually `http://path_to_opauth/keycloak/oauth2callback`


3. Configure your keycloakStrategy.php class the way you prefer.

4. Configure your authentication.php file

5. Option


Strategy configuration
----------------------

Required parameters:

```php
<?php
	'client_id' => 'YOUR CLIENT ID',
	'client_secret' => 'YOUR CLIENT SECRET'
```

Optional parameters:
`auth_endpoint`, `token_endpoint`, `user_info_endpoint`, `redirect_uri`, `scope`, `state`, `access_type`, `approval_prompt`


Authentication configuration
----------------------
```
<?php
return [
    'auth.provider' => 'OpauthKeyCloak',
    'auth.config' => array(
        'logout_url'            => 'https://domainkeycloak/auth/realms/your_realms/protocol/openid-connect/logout',
        'client_id'             => 'your_cliente_id',
        'client_secret'         => 'your_cliente_secret',
        'auth_endpoint'         => 'https://domainkeycloak/auth/realms/your_realms/protocol/openid-connect/auth',
        'token_endpoint'        => 'https://domainkeycloak/auth/realms/your_realms/protocol/openid-connect/token',
        'user_info_endpoint'    => 'https://domainkeycloak/auth/realms/your_realms/protocol/openid-connect/userinfo',
        'redirect_uri'          => 'http://domain_your_application/autenticacao/your_route/oauth2callback',
     ),
```

References
----------
- [Using OAuth 2.0 to Access LoginCidadao APIs](https://developers.loginCidadao.com/accounts/docs/OAuth2)
- [Using OAuth 2.0 for Login](https://developers.loginCidadao.com/accounts/docs/OAuth2Login#scopeparameter)
- [Using OAuth 2.0 for Web Server Applications](https://developers.loginCidadao.com/accounts/docs/OAuth2WebServer)


[1]: https://github.com/uzyn/opauth
