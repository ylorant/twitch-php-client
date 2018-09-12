<?php
namespace TwitchClient\Tests\API\Kraken;

use PHPUnit\Framework\TestCase;
use TwitchClient\API\Kraken\Kraken;
use stdClass;
use Faker;
use TwitchClient\Tests\LoadConfigTrait;

class KrakenStreamsTest extends TestCase
{
    use LoadConfigTrait;

    /**
     * Tests to get stream info for a channel that is supposed to be offline.
     * For this test, the channel that will be taken is the channel configured in the ACCESS_CHANNEL. Make sure that
     * channel isn't streaming while using this test.
     */
    public function testGetOfflineStreamInfo()
    {
        $kraken = new Kraken(self::$tokenProvider);
        $streamInfo = $kraken->streams->info(ACCESS_CHANNEL);
        $this->assertNull($streamInfo);
    }
}