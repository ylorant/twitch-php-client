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
        
        // Comparing games, use strtolower as sometimes games that should be equal have uppercase mismatch
        foreach($gameStreamList as $gameStream) {
            $this->assertEquals(strtolower($stream->game), strtolower($gameStream->game));
        }

        return $stream->channel->name;
    }

    /**
     * Tries to get a stream that is online. Relies on the previous test that gets online streams to feed it.
     * 
     * @depends testGetStreamList
     */
    public function testGetOnlineStreamInfo($user)
    {
        $kraken = new Kraken(self::$tokenProvider);
        $stream = $kraken->streams->info($user);

        $this->assertNotNull($stream);
    }

    /**
     * Tests getting the stream summary for all games along as for a specific game.
     */
    public function testGetStreamsSummary()
    {
        $kraken = new Kraken(self::$tokenProvider);

        $streamStats = $kraken->streams->summary();
        $this->assertNotNull($streamStats);
        $this->assertObjectHasAttribute('channels', $streamStats);
        $this->assertObjectHasAttribute('viewers', $streamStats);

        $this->markTestIncomplete("Stream summary for games doesn't work anymore on twitch, but still documented.");

        // Using Fortnite as there always should be someone streaming that game
        $gameStreamStats = $kraken->streams->summary('Fortnite');
        $this->assertNotNull($gameStreamStats);
        $this->assertNotEquals(false, $gameStreamStats);
        $this->assertNotEquals($streamStats->channels, $gameStreamStats->channels);
        $this->assertNotEquals($streamStats->viewers, $gameStreamStats->viewers);
    }
}