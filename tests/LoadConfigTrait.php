<?php
namespace TwitchClient\Tests;

use TwitchClient\Authentication\DefaultTokenProvider;
use TwitchClient\API\Kraken\Kraken;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use TwitchClient\Client;
use Monolog\Formatter\LineFormatter;

trait LoadConfigTrait
{
    protected static $tokenProvider = null;
    protected static $loggerHandler = null;

    /**
     * Sets up common used parts in tests (token provider, logger).
     */
    public static function setUpBeforeClass()
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
        
        self::$loggerHandler = new StreamHandler(__DIR__ . "/../client_log.txt", Logger::DEBUG);
        $output = "[%datetime%] %level_name%: %message%\n";
        $formatter = new LineFormatter($output, "Y-m-d H:i:s");
        self::$loggerHandler->setFormatter($formatter);

        Client::getLogger()->pushHandler(self::$loggerHandler);
    }
}