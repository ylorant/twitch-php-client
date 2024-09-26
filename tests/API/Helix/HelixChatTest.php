<?php
namespace TwitchClient\Tests\API\Helix;

use PHPUnit\Framework\TestCase;
use TwitchClient\API\Helix\Helix;
use TwitchClient\Tests\LoadConfigTrait;

class HelixChatTest extends TestCase
{
    use LoadConfigTrait;

    public function testSendShoutout()
    {
        $helix = new Helix(self::$tokenProvider);
        $sended = $helix->chat->shoutout(ACCESS_CHANNEL, EMPTY_CHANNEL);

        $this->assertTrue($sended);
    }
}