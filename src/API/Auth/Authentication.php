<?php
namespace TwitchClient\API\Auth;

use TwitchClient\Client;

/**
 * Authentication API client for Twitch.
 * Allows an app to use Twitch's authentication api and workflow to get access tokens for the other APIs.
 * It uses the OAuth 2 Authorization code flow.
 */
class Authentication extends Client
{
    const AUTH_BASEURL = "https://id.twitch.tv/oauth2";

    /**
     * {@inheritdoc}
     */
    public function getTokenHeader($target)
    {
        return null;
    }

    /**
     * Override of the base query method, to prepend the path to the API to the request.
     * 
     * @see Client::query()
     */
    public function query($type, $url, array $parameters = [], $accessChannel = null, $skipTokenRefresh = false)
    {
        return parent::query($type, self::AUTH_BASEURL . '/' . $url, $parameters, $accessChannel, $skipTokenRefresh);
    }

    /**
     * Generates an URL to send the user to the authorization page, allowing to request a new authorization code.
     * It uses the "code" response type.
     * 
     * @param string $redirectUri The redirection URI that has been entered on the application settings.
     * @param array  $scopes      The list of scopes the app should be allowed to get access to.
     * @return string The authorize URL as a string.
     */
    public function getAuthorizeURL($redirectUri, $scopes = [])
    {
        // Building parameter list as an array + http_build_query() because it's cleaner than concatenation
        $query = [
            "response_type" => "code",
            "client_id" => $this->tokenProvider->getClientID(),
            "redirect_uri" => $redirectUri,
            "scope" => implode(' ', $scopes)
        ];

        return self::AUTH_BASEURL. '/authorize?'. http_build_query($query);
    }

    /**
     * Processes a reply from the authorize page. It can be used as a replacement for the getAccessToken() call
     * if you want a more automatic handling of the reply from the authorize page. It'll read the needed data from
     * the GET parameters. The page this function is called from needs to have the URI matching the redirect_uri
     * set on Twitch, or to give it as a parameter.
     * 
     * @param string $redirectUri The redirect URI set into the Twitch app settings, or null to use the current URI.
     * 
     * @return array|false The access token / refresh token combo like the getAccessToken() method, or false if
     *                     an error occurs.
     */
    public function getAccessTokenFromReply($redirectUri = null)
    {
        $code = $_GET['code'] ?? null;
        
        if(empty($redirectUri)) {
            $redirectUri = explode('?', $_SERVER['REQUEST_URI']);
            $redirectUri = $redirectUri[0];
        }

        if(empty($code)) {
            return false;
        }

        return $this->getAccessToken($code, $redirectUri);
    }

    /**
     * Gets an access token from an authorization code.
     * 
     * @param string $code        The authorization code, usually sent from the authorize page.
     * @param string $redirectUri The redirect URI of the application set on the app settings on Twitch.
     * 
     * @return array|false An array containing 2 elements, or false if an error occurs. The array elements are these:
     *                     - On the 'token' key, the actual access token.
     *                     - On the 'refresh' key, the refresh token that will be used when the token expires.
     */
    public function getAccessToken($code, $redirectUri = null)
    {
        $reply = $this->query(Client::QUERY_TYPE_POST, 'token', [
            'client_id' => $this->tokenProvider->getClientID(),
            'client_secret' => $this->tokenProvider->getClientSecret(),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri
        ]);

        if(empty($reply)) {
            return false;
        }

        $token = [
            'token' => $reply->access_token,
            'refresh' => $reply->refresh_token
        ];

        return $token;
    }

    /**
     * Gets an access token to the Twitch API using the OAuth credentials scheme.
     * 
     * @param array $scopes The scopes to get the credentials for.
     * 
     * @return array|false An array containing 2 elements, or false if an error occurs. The array elements are these:
     *                     - On the 'token' key, the actual access token.
     *                     - On the 'refresh' key, the refresh token that will be used when the token expires.
     */
    public function getClientCredentialsToken($scopes = [])
    {
        $reply = $this->query(Client::QUERY_TYPE_POST, 'token', [
            'client_id' => $this->tokenProvider->getClientID(),
            'client_secret' => $this->tokenProvider->getClientSecret(),
            'grant_type' => 'client_credentials',
            'scope' => implode(' ', $scopes)
        ]);

        if(empty($reply)) {
            return false;
        }
        
        $token = [
            'token' => $reply->access_token,
            'refresh' => $reply->refresh_token ?? "" // Default value for refresh_token as apparently the doc
        ];                                           // And the actual behavior don't match up

        return $token;
    }

    /**
     * Refreshes a target's token 
     */
    public function refreshToken($target)
    {
        $refreshToken = $this->tokenProvider->getRefreshToken($target);

        $reply = $this->query(Client::QUERY_TYPE_POST, 'token', [
            'client_id' => $this->tokenProvider->getClientID(),
            'client_secret' =>  $this->tokenProvider->getClientSecret(),
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken
        ], null, true);

        if (empty($reply)) {
            return false;
        }

        // We got a reply, the token is refreshed, save it
        $this->tokenProvider->setAccessToken($target, $reply->access_token);
        $this->tokenProvider->setRefreshToken($target, $reply->refresh_token);

        return $reply->refresh_token;
    }
}