<?php
namespace TwitchClient\Tests;

use PHPUnit\Framework\TestCase;
use TwitchClient\Tests\Fixtures\FlawedClient;
use TwitchClient\Client;

class ErrorTest extends TestCase
{
    use LoadConfigTrait;

    /**
     * Tests the client having an error.
     */
    public function testClientError()
    {
        $kraken = new FlawedClient(self::$tokenProvider);
        $result = $kraken->query(Client::QUERY_TYPE_GET, "/streams/summary", [], null, true);

        $this->assertFalse($result);
    }
}