<?php
namespace TwitchClient\Tests\API\Helix;

use PHPUnit\Framework\TestCase;
use TwitchClient\Tests\LoadConfigTrait;
use TwitchClient\API\Helix\Helix;

class HelixWhispersTest extends TestCase
{
    use LoadConfigTrait;

    public function testSendWhisper()
    {
        $helix = new Helix(self::$tokenProvider);
        $result = $helix->whispers->sendWhisper(ACCESS_CHANNEL, EMPTY_CHANNEL, "Test whisper");

        $this->assertTrue($result);
    }

    public function testSendWhisperFail()
    {
        $helix = new Helix(self::$tokenProvider);
        $result = $helix->whispers->sendWhisper(ACCESS_CHANNEL, ACCESS_CHANNEL, "Test whisper");

        $this->assertFalse($result);
    }
}