<?php
namespace TwitchClient\Tests\Fixtures;

use TwitchClient\Client;
use TwitchClient\Authentication\TokenProvider;

class FlawedClient extends Client
{
    const BASEURL = "https://api.dsjhkjashlasdfh.tv/kraken";
    const RETURN_MIMETYPE = "application/vnd.twitchtv.v5+json";

    /**
     * Constructor, will do a scan of the services directory to discover them.
     */
    public function __construct(TokenProvider $tokenProvider)
    {
        parent::__construct($tokenProvider);

        // Set the base HTTP headers on the underlying client
        $this->setBaseHeaders([
            "Accept" => self::RETURN_MIMETYPE,
            "Client-ID" => $tokenProvider->getClientID()
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getTokenHeader($token)
    {
        return "Authorization: OAuth " . $token;
    }

    /**
     * Override of the base query method, to prepend the flawed URL to the query URL.
     * 
     * @see Client::query()
     */
    public function query($type, $url, array $parameters = [], $accessChannel = null, $skipTokenRefresh = false)
    {
        // Only append the Kraken base URL if it's not already present in it
        if(strpos($url, self::BASEURL) === false) {
            $url = self::BASEURL . $url;
        }

        return parent::query($type, $url, $parameters, $accessChannel, $skipTokenRefresh);
    }
}