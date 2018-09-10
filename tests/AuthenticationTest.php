<?php
namespace TwitchClient\Tests;

use PHPUnit\Framework\TestCase;
use TwitchClient\API\Auth\Authentication;


class AuthenticationTest extends TestCase
{
    use LoadConfigTrait;

    /**
     * Tests the behavior of the default token provider.
     */
    public function testDefaultTokenProvider()
    {
        // Force to do the setup again in the test context to register the code coverage
        self::setUpBeforeClass();
        
        // Testing access token fetching
        $accessToken = self::$tokenProvider->getAccessToken(ACCESS_CHANNEL);
        $unknownAccessToken = self::$tokenProvider->getAccessToken("nonExistentChannel");
        $this->assertEquals(ACCESS_TOKEN, $accessToken);
        $this->assertNull($unknownAccessToken);

        // Testing refresh token fetching
        $refreshToken = self::$tokenProvider->getRefreshToken(ACCESS_CHANNEL);
        $unknownRefreshToken = self::$tokenProvider->getRefreshToken("nonExistentChannel");
        $this->assertEquals(ACCESS_REFRESH, $refreshToken);
        $this->assertNull($unknownRefreshToken);

        // Testing token setting
        $randomAccessChannel = uniqid();
        $randomRefreshChannel = uniqid();
        $randomAccessToken = uniqid();
        $randomRefreshToken = uniqid();
        self::$tokenProvider->setAccessToken($randomAccessChannel, $randomAccessToken);
        self::$tokenProvider->setRefreshToken($randomRefreshChannel, $randomRefreshToken);

        $fetchedAccessToken = self::$tokenProvider->getAccessToken($randomAccessChannel);
        $fetchedRefreshToken = self::$tokenProvider->getRefreshToken($randomRefreshChannel);

        $this->assertEquals($randomAccessToken, $fetchedAccessToken);
        $this->assertEquals($randomRefreshToken, $fetchedRefreshToken);
    }

    /**
     * Tests the authentication flow. It will mainly test failing cases, since successful testing cannot be done without
     * user interaction.
     */
    public function testAuthentication()
    {
        $authApi = new Authentication(self::$tokenProvider);
        
        // Test getting the authorize URL
        $authorizeURL = $authApi->getAuthorizeURL("http://localhost/");
        $this->assertNotNull($authorizeURL);

        // Test getting back a code from GET data
        $_SERVER['REQUEST_URI'] = "http://localhost/";
        $this->assertFalse($authApi->getAccessTokenFromReply());
        $_GET['code'] = uniqid();
        $this->assertFalse($authApi->getAccessTokenFromReply());

    }
}