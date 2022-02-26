<?php
namespace TwitchClient\Tests\Authentication;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use TwitchClient\API\Auth\Authentication;
use TwitchClient\Tests\LoadConfigTrait;

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

    /**
     * Tests refreshing a token from the API.
     */
    public function testRefreshToken()
    {
        $oldAccessToken = self::$tokenProvider->getAccessToken(ACCESS_CHANNEL);
        $oldRefreshToken = self::$tokenProvider->getRefreshToken(ACCESS_CHANNEL);

        $authApi = new Authentication(self::$tokenProvider);
        $reply = $authApi->refreshToken(ACCESS_CHANNEL);
        $newAccessToken = self::$tokenProvider->getAccessToken(ACCESS_CHANNEL);
        $newRefreshToken = self::$tokenProvider->getRefreshToken(ACCESS_CHANNEL);

        $this->assertNotNull($reply);
        $this->assertEquals($reply, $newRefreshToken);
        $this->assertNotEquals($oldAccessToken, $newAccessToken);
        $this->assertEquals($oldRefreshToken, $newRefreshToken);

        // Test bad refresh
        $badReply = $authApi->refreshToken(uniqid());
        $this->assertFalse($badReply);
    }

    public function testClientCredentials()
    {
        $authApi = new Authentication(self::$tokenProvider);
        $reply = $authApi->getClientCredentialsToken();

        $this->assertIsArray($reply);
        $this->assertArrayHasKey('token', $reply);
        $this->assertNotEmpty($reply['token']);
    }
}