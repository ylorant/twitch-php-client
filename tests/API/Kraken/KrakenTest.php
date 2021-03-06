<?php
namespace TwitchClient\Tests\API\Kraken;

use PHPUnit\Framework\TestCase;
use TwitchClient\API\Kraken\Kraken;
use TwitchClient\Tests\LoadConfigTrait;


class KrakenTest extends TestCase
{
    use LoadConfigTrait;

    /**
     * Tests getting a service that does not exist.
     */
    public function testGetNonExistentService()
    {
        $kraken = new Kraken(self::$tokenProvider);
        $nonExistentService = $kraken->nonExistentService;

        $this->assertNull($nonExistentService);
    }

    /**
     * Tests the building of a query string, and that it follows Twitch's Kraken API rules in that regard.
     */
    public function testBuildQueryString()
    {
        $expected = "param1=value1&param2=value2,value3,value4";
        $sourceData = [
            "param1" => "value1",
            "param2" => [
                "value2",
                "value3",
                "value4"
            ]
        ];

        $kraken = new Kraken(self::$tokenProvider);
        $result = urldecode($kraken->buildQueryString($sourceData));

        $this->assertEquals($expected, $result);
    }
}