<?php
namespace TwitchClient\Tests\API\Kraken;

use PHPUnit\Framework\TestCase;
use TwitchClient\API\Kraken\Kraken;
use stdClass;
use TwitchClient\Tests\LoadConfigTrait;
use TwitchClient\API\Kraken\Services\Users;
use ReflectionClass;

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
        $fetchedUserId = $kraken->users->fetchUserId(ACCESS_CHANNEL, $userInfo);

        $this->assertEquals($userId, $fetchedUserId);
        $this->assertNotNull($userInfo);
    }

    /**
     * Tests getting the User ID from the cache.
     * 
     * @depends testSelfUserInfo
     */
    public function testUserIdCache($userId)
    {
        $testedUserId = null;
        $kraken = new Kraken(self::$tokenProvider);
        $testedUserId = $kraken->users->getUserId(ACCESS_CHANNEL);

        $this->assertEquals($userId, $testedUserId);

        // Inspect the user cache to check the existence of the ID
        $reflectionClass = new ReflectionClass(Users::class);
        $staticProperties = $reflectionClass->getStaticProperties();
        $userIdCache = $staticProperties['userIdCache'];

        $this->assertArrayHasKey(ACCESS_CHANNEL, $userIdCache);
    }

    /**
     * Tests fetching an user from it's ID.
     * 
     * @depends testSelfUserInfo
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

        // Test fetching the user by its username (and the cache by the way, since it should be in it after all tests)
        $newUserInfo = $kraken->users->info($userInfo->name);
        $this->assertEquals($userInfo, $newUserInfo);

        // Tests fetching an unknown user (thus failing the cache)
        $nonExistentUser = $kraken->users->info("NonExistentUser". uniqid());
        $this->assertFalse($nonExistentUser);

        // Then drop the cache and test again a valid user to force an empty cache
        $kraken->users->emptyIdCache();
        
        // Assert the cache has been emptied
        $reflectionClass = new ReflectionClass(Users::class);
        $staticProperties = $reflectionClass->getStaticProperties();
        $userIdCache = $staticProperties['userIdCache'];
        $this->assertArrayNotHasKey($userInfo->name, $userIdCache);

        $nonCachedUserInfo = $kraken->users->info($userInfo->name);
        $this->assertEquals($userInfo, $nonCachedUserInfo);
    }
}