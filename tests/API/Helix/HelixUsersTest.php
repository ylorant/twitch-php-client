<?php
namespace TwitchClient\Tests\API\Helix;

use PHPUnit\Framework\TestCase;
use TwitchClient\Tests\LoadConfigTrait;
use TwitchClient\API\Helix\Helix;
use stdClass;
use TwitchClient\API\Helix\Services\Users;
use ReflectionClass;

class HelixUsersTest extends TestCase
{
    use LoadConfigTrait;

    // User info structure
    const USER_INFO_OBJ_ATTRIBUTES = ['id', 'login', 'display_name', 'type', 'description'];

    /**
     * Tests that we can fetch an user's info by its nickname. It will reply the user name for chaining.
     */
    public function testSelfUserInfo()
    {
        // Test own user
        $helix = new Helix(self::$tokenProvider);
        $helix->setDefaultTarget(ACCESS_CHANNEL);

        $user = $helix->users->getUser();

        $this->assertInstanceOf(stdClass::class, $user);

        // Check user info structure.
        foreach (self::USER_INFO_OBJ_ATTRIBUTES as $attr) {
            $this->assertObjectHasAttribute($attr, $user);
        }

        $this->assertEquals(ACCESS_CHANNEL, $user->login);

        return $user->id;
    }

    /**
     * Tests that fetching the user ID with the Users::fetchUserId() is correct.
     * 
     * @depends testSelfUserInfo
     */
    public function testUserId($userId)
    {

        $helix = new Helix(self::$tokenProvider);
        $fetchedUserId = $helix->users->fetchUserId(ACCESS_CHANNEL);

        $this->assertEquals($userId, $fetchedUserId);
    }

    /**
     * Tests getting the User ID from the cache.
     * 
     * @depends testSelfUserInfo
     */
    public function testUserIdCache($userId)
    {
        $helix = new Helix(self::$tokenProvider);
        $testedUserId = $helix->users->getUserId(ACCESS_CHANNEL);

        $this->assertEquals($userId, $testedUserId);

        // Inspect the user cache to check the existence of the ID
        $reflectionClass = new ReflectionClass(Users::class);
        $staticProperties = $reflectionClass->getStaticProperties();
        $userIdCache = $staticProperties['userIdCache'];

        $this->assertArrayHasKey(ACCESS_CHANNEL, $userIdCache);

        // Empty the cache and try again to see if it works on an empty cache
        $helix->users->emptyIdCache();
        $testedNewUserId = $helix->users->getUserId(ACCESS_CHANNEL);
        
        $this->assertEquals($testedUserId, $testedNewUserId);

        return $userId;
    }

    /**
     * Tests getting an user info by various other means.
     * 
     * @depends testUserIdCache
     */
    public function testGetUserInfo($userId)
    {
        $helix = new Helix(self::$tokenProvider);

        // Tests getting an user by its ID
        $userData = $helix->users->getUser($userId);
        $this->assertEquals(ACCESS_CHANNEL, $userData->login);
        
        // Check user info structure.
        foreach (self::USER_INFO_OBJ_ATTRIBUTES as $attr) {
            $this->assertObjectHasAttribute($attr, $userData);
        }

        // Tests getting an user by its name
        $newUserData = $helix->users->getUser($userData->login);
        $this->assertEquals($userData, $newUserData);

        // Empty the cache, assert it's empty, and fetch again the valid user
        $helix->users->emptyIdCache();
        
        // Assert the cache has been emptied
        $reflectionClass = new ReflectionClass(Users::class);
        $staticProperties = $reflectionClass->getStaticProperties();
        $userIdCache = $staticProperties['userIdCache'];
        $this->assertArrayNotHasKey($userData->login, $userIdCache);

        $nonCachedUserData = $helix->users->getUser($userData->login);
        $this->assertEquals($userData, $nonCachedUserData);
    }

    public function testFetchWrongUsers()
    {
        $helix = new Helix(self::$tokenProvider);

        // Tests fetching an unknown user (thus failing the cache)
        $nonExistentUser = $helix->users->getUser("NonExistentUser". uniqid());
        $this->assertFalse($nonExistentUser);

        $nonExistentUser2 = $helix->users->getUserId("NonExistentUser". uniqid());
        $this->assertFalse($nonExistentUser2);
    }
}