<?php

namespace TwitchClient\API\Kraken;

use ReflectionClass;
use TwitchClient\Authentication\TokenProvider;
use TwitchClient\Client;

/**  Twitch Kraken (v5) API client base.
 * Allows to query Twitch's Kraken API servers via HTTP. This class is using Twitch's self-describing API to build queries,
 * along with PHP's magic methods to get names, it'll have mostly auto-building magic shenanigans in it.
 *
 * To get all of Twitch's API capabilities, go check their doc there :
 * https://dev.twitch.tv/docs/v5/
 */
class Kraken extends Client
{
    protected $services = array();

    const KRAKEN_BASEURL = "https://api.twitch.tv/kraken";
    const RETURN_MIMETYPE = "application/vnd.twitchtv.v5+json";

    // Useful constants
    const SERVICES_NAMESPACE = "Services";

    /**
     * Constructor, will do a scan of the services directory to discover them.
     */
    public function __construct(TokenProvider $tokenProvider)
    {
        parent::__construct($tokenProvider);
        $this->discoverServices();

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
     * Override of the base query method, to prepend the Kraken URL to the query URL.
     * 
     * @see Client::query()
     */
    public function query($type, $url, array $parameters = [], $accessChannel = null, $skipTokenRefresh = false)
    {
        // Only append the Kraken base URL if it's not already present in it
        if(strpos($url, self::KRAKEN_BASEURL) === false) {
            $url = self::KRAKEN_BASEURL . $url;
        }

        return parent::query($type, $url, $parameters, $accessChannel, $skipTokenRefresh);
    }

    /**
     * Discovers the available services, by analyzing the folder where they are supposed to be kept.
     * FIXME: Use a RecursiveDirectoryIterator here
     */
    public function discoverServices()
    {
        $servicesPath = __DIR__ . '/' . self::SERVICES_NAMESPACE;
        $dirContents = scandir($servicesPath);

        // Getting self namespace
        $reflectionClass = new ReflectionClass($this);
        $servicesNamespace = $reflectionClass->getNamespaceName() . '\\' . self::SERVICES_NAMESPACE;
        foreach ($dirContents as $item) {
            // Only valable for classes
            if (is_file($servicesPath . '/' . $item) && strpos($item, '.php') === strlen($item) - 4) {
                $className = $servicesNamespace . '\\' . substr($item, 0, -4);
                $serviceName = $className::getServiceName();

                if (!empty($serviceName) && !isset($this->services[$serviceName])) {
                    $this->services[$serviceName] = new $className($this);
                }
            }
        }
    }

    /**
     * Gets a service handler.
     *
     * @param string $serviceName The service name to get.
     * @return Service|null The service handler if found, else null.
     */
    public function getService($serviceName)
    {
        if (isset($this->services[$serviceName])) {
            return $this->services[$serviceName];
        } else {
            return null;
        }
    }

    /**
     * Proxy getter magic method, to alias property access as services on this class.
     * 
     * @param string $name The name of the var accessed. Here, it's the service name.
     * 
     * @return Service|null The service handler if found, else null.
     */
    public function __get($name)
    {
        return $this->getService($name);
    }
}
