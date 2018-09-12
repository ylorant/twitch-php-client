<?php
namespace TwitchClient\Tests\API\Kraken;

use PHPUnit\Framework\TestCase;
use TwitchClient\API\Kraken\Kraken;
use stdClass;
use Faker;
use TwitchClient\Tests\LoadConfigTrait;

class KrakenChannelsTest extends TestCase
{
    use LoadConfigTrait;

    /**
     * Tests getting channel info from Twitch on the access target channel.
     */
    public function testGetSelfChannelInfo()
    {
        $kraken = new Kraken(self::$tokenProvider);
        $kraken->setDefaultTarget(ACCESS_CHANNEL);

        $channelInfo = $kraken->channels->info();
        
        $this->assertNotNull($channelInfo);
        $this->assertInstanceOf(stdClass::class, $channelInfo);
        $this->assertEquals(ACCESS_CHANNEL, $channelInfo->name);

        return $channelInfo->_id;
    }

    /**
     * Tests getting a channel's info by its id
     * 
     * @depends testGetSelfChannelInfo
     */
    public function testGetChannelInfoById($channelId)
    {
        $kraken = new Kraken(self::$tokenProvider);
        $channelInfo = $kraken->channels->info($channelId);

        $this->assertNotEmpty($channelInfo);
        $this->assertInstanceOf(stdClass::class, $channelInfo);
        $this->assertEquals($channelId, $channelInfo->_id);
        $this->assertEquals(ACCESS_CHANNEL, $channelInfo->name);

        // Trying to check if getting a non-existent channel results in null
        $nonExistentChannel = $kraken->channels->info(-1);
        $this->assertFalse($nonExistentChannel);

        return $channelInfo->name;
    }

    /**
     * Tests getting a channel by its name.
     * 
     * @depends testGetChannelInfoById
     */
    public function testGetChannelInfoByName($name)
    {
        $kraken = new Kraken(self::$tokenProvider);
        $channelInfo = $kraken->channels->info($name);

        $this->assertNotEmpty($channelInfo);
        $this->assertInstanceOf(stdClass::class, $channelInfo);
        $this->assertEquals(ACCESS_CHANNEL, $channelInfo->name);

        // Try to get info for a non-existent channel
        $nonExistentChannelInfo = $kraken->channels->info(uniqid());
        $this->assertFalse($nonExistentChannelInfo);
    }

    /**
     * Tests getting a channel's followers, by the channel ID
     * 
     * @depends testGetSelfChannelInfo
     */
    public function testGetChannelFollowersById($channelId)
    {
        $kraken = new Kraken(self::$tokenProvider);
        $channelFollowers = $kraken->channels->followers($channelId);
        $this->assertNotEmpty($channelFollowers);
        
        // Try to get followers for a non-existent channel
        $unknownChannelFollowers = $kraken->channels->followers(-1);
        $this->assertFalse($unknownChannelFollowers);
    }

    /**
     * Tests getting a channel's followers, by the channel name.
     * 
     * @depends testGetChannelInfoById
     */
    public function testGetChannelFollowersByName($name)
    {
        $kraken = new Kraken(self::$tokenProvider);
        $channelFollowers = $kraken->channels->followers($name);
        $this->assertNotEmpty($channelFollowers);

        // Try to get followers for a non-existent channel
        $unknownChannelFollowers = $kraken->channels->followers("NonExistentUser". uniqid());
        $this->assertFalse($unknownChannelFollowers);
    }

    /**
     * Tests updating the channel title.
     */
    public function testUpdateChannelTitle()
    {
        $kraken = new Kraken(self::$tokenProvider);
        $faker = Faker\Factory::create();

        $newTitle = $faker->sentence();
        $oldInfo = $kraken->channels->info(ACCESS_CHANNEL);
        $updatedInfo = $kraken->channels->update(ACCESS_CHANNEL, ['status' => $newTitle]);
        
        $this->assertNotEmpty($updatedInfo);
        $this->assertEquals(ACCESS_CHANNEL, $updatedInfo->name);
        $this->assertEquals($newTitle, $updatedInfo->status);
    }
}