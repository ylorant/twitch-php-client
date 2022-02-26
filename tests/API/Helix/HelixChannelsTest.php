<?php
namespace TwitchClient\Tests\API\Helix;

use PHPUnit\Framework\TestCase;
use TwitchClient\API\Helix\Helix;
use TwitchClient\Tests\LoadConfigTrait;
use Faker\Factory as FakerFactory;
use InvalidArgumentException;
use stdClass;

class HelixChannelsTest extends TestCase
{
    use LoadConfigTrait;

    public function testGetSingleInfo()
    {
        $helix = new Helix(self::$tokenProvider);

        $channelInfo = $helix->channels->info(ACCESS_CHANNEL);
        
        $this->assertNotNull($channelInfo);
        $this->assertInstanceOf(stdClass::class, $channelInfo);
        $this->assertEquals(ACCESS_CHANNEL, $channelInfo->broadcaster_name);

        return $channelInfo->broadcaster_id;
    }

    /**
     * Tests fetching multiple channels, one with its ID and one with its name.
     * 
     * @depends testGetSingleInfo 
     */
    public function testGetMultipleInfo($channelId)
    {
        $helix = new Helix(self::$tokenProvider);

        $channelInfo = $helix->channels->info([$channelId, EMPTY_CHANNEL]);
        
        $this->assertNotNull($channelInfo);
        $this->assertCount(2, $channelInfo);
    }

    /**
     * Tests updating the channel title.
     */
    public function testUpdateChannelTitle()
    {
        $helix = new Helix(self::$tokenProvider);
        $faker = FakerFactory::create();

        $newTitle = $faker->sentence();
        $updated = $helix->channels->update(ACCESS_CHANNEL, ['title' => $newTitle]);
        
        $this->assertTrue($updated);
        
        // Fetch the new info to check it has been updated correctly
        $newInfo = $helix->channels->info(ACCESS_CHANNEL);

        $this->assertEquals(ACCESS_CHANNEL, $newInfo->broadcaster_name);
        $this->assertEquals($newTitle, $newInfo->title);
    }

    public function testUpdateNothing()
    {
        $this->expectException(InvalidArgumentException::class);

        $helix = new Helix(self::$tokenProvider);
        $updated = $helix->channels->update(ACCESS_CHANNEL, []);
    }

    /**
     * Tests starting a commercial on a channel.
     */
    public function testStartCommercial()
    {
        $helix = new Helix(self::$tokenProvider);
        $started = $helix->channels->startCommercial(ACCESS_CHANNEL, 60);

        $this->assertTrue($started);
    }

    public function testStartCommercialError()
    {
        $this->expectException(InvalidArgumentException::class);

        $helix = new Helix(self::$tokenProvider);
        $started = $helix->channels->startCommercial(ACCESS_CHANNEL, 66);
    }
}