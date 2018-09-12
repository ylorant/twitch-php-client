<?php
namespace TwitchClient;

use TwitchClient\Authentication\TokenProvider;
use TwitchClient\API\Auth\Authentication;
use Psr\Log\LoggerInterface;
use TwitchClient\Log\DummyLogger;

abstract class Client
{
    protected $tokenProvider;
    protected $baseHeaders;
    protected $defaultTarget;

    /** 
     * @var Logger The Monolog logger for all the clients. It's a singleton that will be instancied at its first 
     *              request by another part of the library.
     */
    protected static $logger;

    // Query types enum
    const QUERY_TYPE_GET = "GET";
    const QUERY_TYPE_POST = "POST";
    const QUERY_TYPE_PUT = "PUT";
    const QUERY_TYPE_DELETE = "DELETE";
    
    // Useful constants
    const DATA_QUERIES = [
        self::QUERY_TYPE_POST,
        self::QUERY_TYPE_PUT
    ]; // Indicates which methods sends their data in the request body

    /**
     * Constructor.
     * 
     * @param TokenProvider $tokenProvider The token provider that will be used to authenticate the requests.
     */
    public function __construct(TokenProvider $tokenProvider)
    {
        $this->baseHeaders = [];
        $this->tokenProvider = $tokenProvider;
    }

    /**
     * Gets the header for the access token.
     * 
     * @param string $token The token.
     * 
     * @return string The header for the access token as a string.
     */
    protected abstract function getTokenHeader($token);

    /**
     * Gets the logger used by the library.
     * 
     * @return Logger The logger used by the library. The first call to this method will create the logger.
     */
    public static function getLogger()
    {
        // If there isn't any logger loaded, we load a dummy
        if (empty(self::$logger)) {
            return new DummyLogger();
        }

        return self::$logger;
    }

    public static function setLogger(LoggerInterface $logger)
    {
        self::$logger = $logger;
    }

    /**
     * Gets the base HTTP headers that should be sent on each request defined in the client instance.
     * 
     * @return array The associative array of base headers defiend in the client.
     */
    public function getBaseHeaders()
    {
        return $this->baseHeaders;
    }

    /**
     * Sets the base headers that should be sent on each request for the client instance.
     * 
     * @param array $headers The associative array containing the base headers.
     */
    public function setBaseHeaders(array $headers)
    {
        $this->baseHeaders = $headers;
    }

    /**
     * Sets the default target. When a default target is set, its access token will be sent with each request, except
     * when another target is specified in the call obviously. of course 
     * 
     * @param string|null $target The target to set as default.
     * 
     * @return bool True if the target has been set successfully, false if not (mainly it doesn't exist).
     */
    public function setDefaultTarget($target = null)
    {
        if (!is_null($target) && !$this->tokenProvider->getAccessToken($target)) {
            return false;
        }

        $this->defaultTarget = $target;
        return true;
    }

    /**
     * Gets the default target which access token is sent with all requests when no override target is defined.
     * 
     * @return string|null The default target or null if there is none.
     */
    public function getDefaultTarget()
    {
        return $this->defaultTarget;
    }

    /**
     * Executes a query on the Twitch API.
     * This method allows to execute directly a query on Twitch's Kraken API.
     * It takes into account whether it was called from
     * a defined service or directly from the root Kraken class, to build the correct path.
     *
     * @param string $type             The query type. You can use Kraken::QUERY_TYPE_* enum values for
     *                                 easier understanding.
     * @param string $url              The endpoint to query.
     * @param array  $parameters       The parameters to give to the query, as a key-value array. Optional.
     * @param string $target           If the used query needs to have an access token linked to it, specifies
     *                                 the target to which the token should have access granted for. Optional.
     * @param bool   $skipTokenRefresh Set this to true if you don't want to perform a token refresh
     *                                 in case of expired token.
     * 
     * @return mixed The API response, as an object translated from the JSON, or false if the request fails.
     */
    public function query($type, $url, array $parameters = [], $target = null, $skipTokenRefresh = false)
    {
        $tries = 0;
        $replyCode = 0;
        $callUrl = $url;

        // For GET queries, append parameters to url as query parameters
        if (!in_array($type, self::DATA_QUERIES) && !empty($parameters)) {
            if (strpos($callUrl, '?') === false) {
                $callUrl .= '?';
            }

            $callUrl .= http_build_query($parameters);
        }

        $callUrl = trim($callUrl, '/'); // Remove any trailing slashes from the url endpoint
        $curl = curl_init($callUrl);

        $httpHeaders = [];

        // Appending base HTTP headers defined by APIs
        foreach ($this->baseHeaders as $headerName => $headerValue) {
            $httpHeaders[] = $headerName . ": ". $headerValue;
        }

        // If no target has been given, set it to the default one
        if (empty($target)) {
            $target = $this->getDefaultTarget();
        }

        // Now, really check if there is a target and apply the needed header
        if (!empty($target)) {
            $accessToken = $this->tokenProvider->getAccessToken($target);
            $authHeader = $this->getTokenHeader($accessToken);

            if (!empty($authHeader)) {
                $httpHeaders[] = $authHeader;
            }
        }

        // Only POSTs and PUTs need to have data defined as body, in JSON
        if (in_array($type, self::DATA_QUERIES)) {
            $httpHeaders[] = 'Content-Type: application/json';
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameters));
        }

        // Set base common options
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $type,
            CURLOPT_HTTPHEADER => $httpHeaders
        ]);

        // Logging the request before sending it
        $logger = self::getLogger();
        $logger->debug(">>> HTTP Query:");
        $logger->debug("Type: ". $type);
        $logger->debug("URL: ". $url);
        $logger->debug("Parameters: ". json_encode($parameters));
        $logger->debug("Target: ". ($target ?? "None"));
        $logger->debug("Skip token refresh: ". ($skipTokenRefresh ? "Yes" : "No"));
        $logger->debug("Generated headers:");

        foreach ($httpHeaders as $header) {
            $logger->debug("- ". $header);
        }

        $logger->debug(""); // Blank log line for lisibility

        // Executing the request
        $reply = curl_exec($curl);
        $replyInfo = curl_getinfo($curl);
        $replyCode = $replyInfo['http_code'];

        // Log reply from server
        $logger->debug("<<< HTTP Response:");
        $logger->debug("HTTP Code: ". $replyCode);
        $logger->debug("Content: ". $reply);
        $logger->debug(""); // Blank log line for lisibility

        // Receiving a 401 code could mean our token is expired. We can try to resolve this by
        // asking a refresh token, saving it and retrying. Of course, we don't do that when the query was
        // actually a token refresh.
        if ($replyCode == 401 && !$skipTokenRefresh) {
            $authenticationAPI = new Authentication($this->tokenProvider);
            $newToken = $authenticationAPI->refreshToken($target);

            // We return false if the new token has not been delivered, since it's considered a query failure.
            if (empty($newToken)) {
                return false;
            }

            // Retry the query now that the access token has been updated, but without refresh this time.
            return $this->query($type, $url, $parameters, $target, true);
        }
        
        // Return false on a non-valid code.
        if ($replyCode != 200) {
            return false;
        }

        return json_decode($reply);
    }
}