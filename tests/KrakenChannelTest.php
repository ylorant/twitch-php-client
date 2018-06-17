<?php
namespace TwitchClient\Tests;

use PHPUnit\Framework\TestCase;
use TwitchClient\API\Kraken\Kraken;
use stdClass;
use Faker;

class KrakenChannelTest extends TestCase
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