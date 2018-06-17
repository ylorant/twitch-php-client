<?php
namespace TwitchClient\Tests;

use PHPUnit\Framework\TestCase;
use TwitchClient\API\Kraken\Kraken;
use stdClass;

class KrakenUsersTest extends TestCase
{
    use LoadConfigTrait;

    // User info structure
    const USER_INFO_OBJ_ATTRIBUTES = ['_id', 'name', 'created_at', 'display_name', 'type', 'bio'];

    /**
     * Tests that we can fetch an user's info by its nickname. It will reply the user name for chaining.
     */
    public function testSelfUserInfo()
    {
        // Test own user
        $kraken = new Kraken(self::$tokenProvider);
        $kraken->setDefaultTarget(ACCESS_CHANNEL);

        $user = $kraken->users->info();

        $this->assertInstanceOf(stdClass::class, $user);

        // Check user info structure.
        foreach (self::USER_INFO_OBJ_ATTRIBUTES as $attr) {
            $this->assertObjectHasAttribute($attr, $user);
        }

        $this->assertEquals(ACCESS_CHANNEL, $user->name);

        return $user->_id;
    }

    /**
     * Tests that fetching the user ID with the Users::getUserId() is correct.
     * Also tests the fetched data from the method, since it can fetch the data too.
     * 
     * @depends testSelfUserInfo
     */
    public function testUserId($userId)
    {
        $userInfo = null;

        $kraken = new Kraken(self::$tokenProvider);
        $fetchedUserId = $kraken->users->getUserId(ACCESS_CHANNEL, $userInfo);

        $this->assertEquals($userId, $fetchedUserId);
        $this->assertNotNull($userInfo);

        return $userId;
    }

    /**
     * Tests fetching an user from it's ID.
     * 
     * @depends testUserId
     */
    public function testUserInfo($userId)
    {
        $kraken = new Kraken(self::$tokenProvider);
        $userInfo = $kraken->users->info($userId);
        
        $this->assertInstanceOf(stdClass::class, $userInfo);
        $this->assertEquals(ACCESS_CHANNEL, $userInfo->name);

        // Check user info structure.
        foreach (self::USER_INFO_OBJ_ATTRIBUTES as $attr) {
            $this->assertObjectHasAttribute($attr, $userInfo);
        }
    }
}