<?php
namespace TwitchClient\Tests;

use TwitchClient\Authentication\DefaultTokenProvider;
use TwitchClient\API\Kraken\Kraken;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use TwitchClient\Client;
use Monolog\Formatter\LineFormatter;
use TwitchClient\API\Auth\Authentication;

trait LoadConfigTrait
{
    protected static $tokenProvider = null;
    protected static $logger = null;

    /**
     * Sets up common used parts in tests (token provider, logger).
     */
    public static function setUpBeforeClass(): void
    {
        self::$tokenProvider = new DefaultTokenProvider(CLIENT_ID, CLIENT_SECRET);
        
        // Building the token array
        $tokenList = [
            ACCESS_CHANNEL => [
                "token" => ACCESS_TOKEN,
                "refresh" => ACCESS_REFRESH
            ]
        ];

        self::$tokenProvider->setTokensDatabase($tokenList);

        // Detting up the test logger using Monolog
        self::$logger = new Logger('twitch-php-client');
        
        $loggerHandler = new StreamHandler(__DIR__ . "/../client_log.txt", Logger::DEBUG);
        
        $output = "[%datetime%] %level_name%: %message%\n";
        $formatter = new LineFormatter($output, "Y-m-d H:i:s");
        
        $loggerHandler->setFormatter($formatter);
        self::$logger->pushHandler($loggerHandler);

        Client::setLogger(self::$logger);

        // Fetch the default token
        $authenticationAPI = new Authentication(self::$tokenProvider);
        $authenticationAPI->getClientCredentialsToken();
    }
}