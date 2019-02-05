<?php
namespace TwitchClient\Tests\API\Helix;

use PHPUnit\Framework\TestCase;
use TwitchClient\Tests\LoadConfigTrait;
use TwitchClient\API\Helix\Helix;

class HelixTest extends TestCase
{
    use LoadConfigTrait;

    /**
     * Tests getting a service that does not exist.
     */
    public function testGetNonExistentService()
    {
        $helix = new Helix(self::$tokenProvider);
        $nonExistentService = $helix->nonExistentService;

        $this->assertNull($nonExistentService);
    }

    /**
     * Tests the building of a query string, and that it follows Twitch's Helix API rules in that regard.
     */
    public function testBuildQueryString()
    {
        $expected = "param1=value1&param2=value2&param2=value3&param2=value4";
        $sourceData = [
            "param1" => "value1",
            "param2" => [
                "value2",
                "value3",
                "value4"
            ]
        ];

        $helix = new Helix(self::$tokenProvider);
        $result = $helix->buildQueryString($sourceData);

        $this->assertEquals($expected, $result);
    }
}