<?php
namespace TwitchClient\Authentication;

/**
 * Default token provider, implements a basic token provider mechanism where any class can
 * set tokens into, and it will deliver them to the API it has been intialized with.
 * 
 * This class is useful if you don't want to implement yourself a dedicated token provider
 * with a more elaborated workflow.
 */
class DefaultTokenProvider implements TokenProvider
{
    /** @var string The app's client ID */
    protected $clientID;
    /** @var string The app's client secret */
    protected $clientSecret;
    /** @var array The access tokens database */
    protected $tokens = [];

    // Constants
    const KEY_TOKEN = 'token';
    const KEY_REFRESH = 'refresh';

    /**
     * Construcctor, initializes the provider with a client ID and a client secret.
     * 
     * @param string $clientID The app's client ID.
     * @param string $clientSecret The app's client secret.
     */
    public function __construct($clientID, $clientSecret)
    {
        $this->clientID = $clientID;
        $this->clientSecret = $clientSecret;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientID()
    {
        return $this->clientID;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($target)
    {
        if(isset($this->tokens[$target])) {
            return $this->tokens[$target][self::KEY_TOKEN];
        }
        
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getRefreshToken($target)
    {
        if(isset($this->tokens[$target])) {
            return $this->tokens[$target][self::KEY_REFRESH];
        }
        
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setAccessToken($target, $token)
    {
        if(empty($this->tokens[$target])) {
            $this->tokens[$target] = [self::KEY_TOKEN => null, self::KEY_REFRESH => null];
        }

        $this->tokens[$target][self::KEY_TOKEN] = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function setRefreshToken($target, $token)
    {
        if(empty($this->tokens[$target])) {
            $this->tokens[$target] = [self::KEY_TOKEN => null, self::KEY_REFRESH => null];
        }

        $this->tokens[$target][self::KEY_REFRESH] = $token;
    }
    
    /**
     * Sets the tokens database in one operation.
     * 
     * @param array $tokens The database of tokens as an associative array, the key being the target, 
     *                      the value being an array having 2 keys :
     *                      - token: The access token
     *                      - refresh: The refresh token
     */
    public function setTokensDatabase(array $tokens)
    {
        $this->tokens = $tokens;
    }
}