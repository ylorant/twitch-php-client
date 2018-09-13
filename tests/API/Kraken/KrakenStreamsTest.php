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

    /**
     * Tests getting stream lists, with and without parameters set to reduce the list of results.
     */
    public function testGetStreamList()
    {
        $kraken = new Kraken(self::$tokenProvider);

        $streamList = $kraken->streams->list();
        $this->assertNotEmpty($streamList);

        // Get the first stream for the next tests, that way we have something we're pretty sure will still be up
        $stream = reset($streamList);

        // Filtering by channel
        $channelStreamList = $kraken->streams->list(["channel" => [$stream->channel->_id]]);
        $this->assertNotEmpty($channelStreamList);
        $this->assertEquals(1, count($channelStreamList));

        // Filtering by game
        $gameStreamList = $kraken->streams->list(["game" => $stream->game]);
        $this->assertNotEmpty($gameStreamList);
        
        foreach($gameStreamList as $gameStream) {
            $this->assertEquals($stream->game, $gameStream->game);
        }
    }
}