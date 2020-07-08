<?php
namespace TwitchClient\API\Helix;

use ReflectionClass;
use TwitchClient\Authentication\TokenProvider;
use TwitchClient\Client;

/**  Twitch Helix API client base.
 * Allows to query Twitch's Helix API through HTTP.
 *
 * To get all of Twitch's API capabilities, go check their doc there :
 * https://dev.twitch.tv/docs/api/
 */
class Helix extends Client
{
    
    protected $services = array();

    // Useful constants
    const HELIX_BASEURL = "https://api.twitch.tv/helix";
    const SERVICES_NAMESPACE = "Services"; // Here, what Twitch calls "Resources" on its docs are called "Services" to maintain consistency
                                           // with the Kraken API.
    const TOKEN_ERROR_CODE = [400, 401];
    
    
    public function __construct(TokenProvider $tokenProvider)
    {
        parent::__construct($tokenProvider);
        $this->discoverServices();

        // Set the base HTTP headers on the underlying client
        $this->setBaseHeaders([
            "Client-ID" => $tokenProvider->getClientID()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenHeader($token)
    {
        return "Authorization: Bearer " . $token;
    }

    
    /**
     * Override of the base query method, to prepend the Helix URL to the query URL.
     * 
     * @see Client::query()
     */
    public function query($type, $url, array $parameters = [], $accessChannel = null, $skipTokenRefresh = false)
    {
        // Only append the Helix base URL if it's not already present in it
        if(strpos($url, self::HELIX_BASEURL) === false) {
            $url = self::HELIX_BASEURL . $url;
        }

        return parent::query($type, $url, $parameters, $accessChannel, $skipTokenRefresh);
    }

    /**
     * Overrides the default client's query string building method to format it as the Helix API requires.
     * 
     * @see Client::buildQueryString()
     */
    public function buildQueryString(array $parameters)
    {
        $queryString = "";

        // We must use our own loop because the API uses a non-standard naming scheme for multi-element arrays that we have to
        // handle manually.
        foreach($parameters as $key => $value) {
            if(is_array($value)) {
                foreach($value as $subvalue) {
                    $queryString .= "&" . $key . "=" . $subvalue;
                }
            } else {
                $queryString .= "&" . $key . "=" . $value;
            }
        }

        // Remove the initial ampersand that the parameters list has.
        $queryString = substr($queryString, 1);

        return $queryString;
    }

    /**
     * Discovers the available services, by analyzing the folder where they are supposed to be kept.
     * FIXME: Maybe do a trait that Helix and Kraken could use instead of copying what is essentially the same code
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