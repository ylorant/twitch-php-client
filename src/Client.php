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
    protected $lastError;

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
    const QUERY_TYPE_PATCH = "PATCH";
    
    // Useful constants
    const DATA_QUERIES = [
        self::QUERY_TYPE_POST,
        self::QUERY_TYPE_PUT,
        self::QUERY_TYPE_PATCH
    ]; // Indicates which methods sends their data in the request body

    // Stores the error code that is sent by the API when a token is expired
    const TOKEN_ERROR_CODE = [];

    // Error constants
    const ERROR_TOKEN_REFRESH_FAILED = 1; // Token renewal failed
    const ERROR_API_ERROR = 2; // Error from the API

    /**
     * Constructor.
     * 
     * @param TokenProvider $tokenProvider The token provider that will be used to authenticate the requests.
     */
    public function __construct(TokenProvider $tokenProvider)
    {
        $this->baseHeaders = [];
        $this->lastError = null;
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
     * Returns the last error the client has encountered.
     * 
     * @return array|null The last error as an array with the 'errno' and 'error' keys, or null if no error occured yet.
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Sets the last error the client has encountered.
     * 
     * @param array $error The new error to set, with the 'errno' and 'error' keys for respectively the error number and the error message.
     * 
     * @return void
     */
    protected function setLastError(array $error)
    {
        $this->lastError = $error;
        self::getLogger()->error("[". $error['errno']. "] ". $error['error']);
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
     * This method allows to execute directly a query on Twitch's API. It is usually overriden by the actual API
     * class for specific usages.
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
        $replyCode = 0;
        $callUrl = $url;

        // For GET queries, append parameters to url as query parameters
        if (!in_array($type, self::DATA_QUERIES) && !empty($parameters)) {
            if (strpos($callUrl, '?') === false) {
                $callUrl .= '?';
            }

            $callUrl .= $this->buildQueryString($parameters);
        }

        $callUrl = trim($callUrl, '/'); // Remove any trailing slashes from the url endpoint
        $curl = curl_init($callUrl);

        $httpHeaders = [];

        // Appending base HTTP headers defined by APIs
        foreach ($this->baseHeaders as $headerName => $headerValue) {
            $httpHeaders[] = $headerName . ": ". $headerValue;
        }

        $accessToken = $this->tokenProvider->getDefaultAccessToken();

        // If no target has been given, use the default token
        if (!empty($target)) {
            $accessToken = $this->tokenProvider->getAccessToken($target);
        } elseif(!empty($this->getDefaultTarget())) {
            $target = $this->getDefaultTarget();
            $accessToken = $this->tokenProvider->getAccessToken($target);
        } else {
            $accessToken = $this->tokenProvider->getDefaultAccessToken();
        }

        // Apply the needed header for the token
        if(!empty($accessToken)) {
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
        
        // If there is no HTTP code, we stop right there since it means there's something wrong with the HTTP query itself
        if(empty($replyCode)) {
            $curlErrno = curl_errno($curl);
            $this->setLastError(['errno' => $curlErrno + 1000, 'error' => "cURL error (". $curlErrno. "): ". curl_error($curl)]);
            return false;
        }

        // Receiving an error code could mean our token is expired. We can try to resolve this by
        // asking a refresh token, saving it and retrying. Of course, we don't do that when the query was
        // actually a token refresh.
        if (in_array($replyCode, static::TOKEN_ERROR_CODE) && !$skipTokenRefresh) {
            // A target is set, we refresh the token for it
            $authenticationAPI = new Authentication($this->tokenProvider);

            if(!empty($target)) {
                $newToken = $authenticationAPI->refreshToken($target);
            } else {
                $newToken = $authenticationAPI->getClientCredentialsToken();
            }

            // We return false if the new token has not been delivered, since it's considered a query failure.
            if (empty($newToken)) {
                $this->setLastError(['errno' => self::ERROR_TOKEN_REFRESH_FAILED, 'error' => "Could not refresh OAuth token"]);
                return false;
            }

            // Retry the query now that the access token has been updated, but without refresh this time.
            return $this->query($type, $url, $parameters, $target, true);
        }
        
        // Return false on a non-valid code.
        if ($replyCode >= 300) {
            $replyData = json_decode($reply);
            $this->setLastError(['errno' => self::ERROR_API_ERROR, 'error' => $replyData->message]);
            return false;
        }

        return !empty($reply) ? json_decode($reply) : true;
    }

    /**
     * Builds the query string that will be sent to the server for queries that don't use an HTTP method that is
     * using body data (typically GET queries).
     * By default, this uses PHP's embedded http_build_query, but it can be overridden by children classes to alter
     * its behaviour.
     * 
     * @param array $parameters The parameters to transform into a query string.
     * 
     * @return string The formed query string.
     */
    public function buildQueryString(array $parameters)
    {
        return http_build_query($parameters);
    }
}
