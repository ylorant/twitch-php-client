<?php
namespace TwitchClient\Tests\API\Helix;

use PHPUnit\Framework\TestCase;
use TwitchClient\API\Helix\Helix;
use TwitchClient\Tests\LoadConfigTrait;
use InvalidArgumentException;

class HelixAdsTest extends TestCase
{
    use LoadConfigTrait;

    /**
     * Tests starting a commercial on a channel.
     */
    public function testStartCommercial()
    {
        $helix = new Helix(self::$tokenProvider);
        $started = $helix->ads->startCommercial(ACCESS_CHANNEL, 60);

        $this->assertTrue($started);
    }

    public function testStartCommercialError()
    {
        $this->expectException(InvalidArgumentException::class);

        $helix = new Helix(self::$tokenProvider);
        $started = $helix->ads->startCommercial(ACCESS_CHANNEL, 66);
    }
}