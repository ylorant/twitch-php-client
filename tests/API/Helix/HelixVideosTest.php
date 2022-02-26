<?php
namespace TwitchClient\Tests\API\Helix;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use TwitchClient\Tests\LoadConfigTrait;
use TwitchClient\API\Helix\Helix;
use TwitchClient\API\Helix\Services\Videos;

class HelixVideosTest extends TestCase
{
    use LoadConfigTrait;

    public function testGetVideos()
    {
        $helix = new Helix(self::$tokenProvider);
        $fetchedUserId = $helix->users->fetchUserId(ACCESS_CHANNEL);

        // Fetch first page
        $fetchedVideos = $helix->videos->getVideos([
            Videos::FILTER_USER_ID => $fetchedUserId,
        ]);
        
        $this->assertIsArray($fetchedVideos);

        $firstVideoData = reset($fetchedVideos);
        $this->assertIsString($firstVideoData->title);
    }

    public function testGetVideosEmpty()
    {
        $helix = new Helix(self::$tokenProvider);
        $fetchedUserId = $helix->users->fetchUserId(EMPTY_CHANNEL);

        $fetchedVideos = $helix->videos->getVideos([
            Videos::FILTER_USER_ID => $fetchedUserId,
        ]);

        $this->assertIsArray($fetchedVideos);
        $this->assertEmpty($fetchedVideos);
    }

    public function testGetVideosError()
    {
        $this->expectException(RuntimeException::class);
        
        $helix = new Helix(self::$tokenProvider);
        $helix->videos->getVideos([]);
    }
}