<?php
namespace TwitchClient\Authentication;

/**
 * TokenProvider interface. This interface describes how the program which uses this library should provide the Twitch
 * tokens to the library. This allows the implementing side to be free of organizing its token storage how it wants.
 * 
 * TODO: handle channel name => id matching for targets. Maybe internally ?
 */
interface TokenProvider
{
    /**
     * Returns the app's client ID used to access Twitch's services.
     * 
     * @return string The app client ID.
     */
    public function getClientID();

    /**
     * Returns the app's client Secret used in sensitive requests.
     * 
     * @return string The app client Secret.
     */
    public function getClientSecret();

    /**
     * Returns the access token for the given target.
     * 
     * @param string $target The target (channel or account) that will need the token to have access to.
     * 
     * @return string|null The token, or null if the token could not be delivered.
     */
    public function getAccessToken($target);

    /**
     * Returns the refresh token for the given target.
     * 
     * @param string $target The target (channel or account) that will need the token to have access to.
     * 
     * @return string|null The token, or null if the token could not be delivered.
     */
    public function getRefreshToken($target);

    /**
     * Sets a new access token for a defined target.
     * 
     * @param string $target The target the token will be set for.
     * @param string $token The new access Token.
     */
    public function setAccessToken($target, $token);

    /**
     * Sets a new refresh token for a defined target.
     * 
     * @param string $target The target the token will be set for.
     * @param string $token The new refresh Token.
     */
    public function setRefreshToken($target, $token);
}