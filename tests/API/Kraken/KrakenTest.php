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
}