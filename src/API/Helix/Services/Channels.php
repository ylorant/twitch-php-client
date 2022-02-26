<?php
namespace TwitchClient\API\Helix\Services;

use InvalidArgumentException;
use TwitchClient\Client;


/**
 * Twitch Helix API resource: Channels.
 * Handles channel information fetching and modification.
 */
class Channels extends Service
{
    const SERVICE_NAME = "channels";
    
    const VALID_COMMERCIAL_LENGTHS = [30, 60, 90, 120, 150, 180];

    /**
     * Gets info for a given channel. 
     * 
     * @param int|string|array $usernamesOrIds The ID(s) or username of the user(s) to get the channel from, up to 100.
     * @return array An array containing info on the fetched channels.
     * 
     * @see https://dev.twitch.tv/docs/api/reference#get-channel-information
     */
    public function info($usernamesOrIds)
    {
        $uniqueUserProvided = false;

        if(!is_array($usernamesOrIds)) {
            $usernamesOrIds = [$usernamesOrIds];
            $uniqueUserProvided = true;
        }
        $userIds = [];
        $usernamesToResolve = [];
        
        // Separate every user ID from the usernames to resolve via the Users API
        foreach($usernamesOrIds as $usernameOrId) {
            if(is_numeric($usernameOrId)) {
                $userIds[] = $usernameOrId;
            } else {
                $usernamesToResolve[] = $usernameOrId;
            }
        }

        // Fetch any username that needs fetching
        if(!empty($usernamesToResolve)) {
            /** @var Users $userApi */
            $userApi = $this->helix->getService(Users::SERVICE_NAME);
            $fetchedUserIds = $userApi->getUsersIds($usernamesToResolve);

            $userIds = array_merge($userIds, $fetchedUserIds);
        }
        
        $parameters = ['broadcaster_id' => $userIds];
        $result = $this->helix->query(Client::QUERY_TYPE_GET, "/channels", $parameters);

        if($uniqueUserProvided) {
            return $result->data[0] ?? null;
        }

        return $result->data;
    }

    /**
     * Updates a given channel information.
     * 
     * @param int|string $usernameOrId The username or ID to update the channel for.
     * @param array $newData The new data. At least one element to update must be specified. Available fields:
     *                       - game_id: The ID for the currently shown category/game.
     *                       - broadcaster_language: The broadcast language.
     *                       - title: The stream title. If provided, cannot be empty.
     *                       - delay: Force a stream delay, only available for partner streams.
     * @param string|null $authenticationChannel The authentication channel to get the client token from in the
     *                                           token provider.
     * @return bool True if the update succeeded. Will throw an exception on error. 
     * 
     * @see https://dev.twitch.tv/docs/api/reference#modify-channel-information
     */
    public function update($usernameOrId, array $newData, $authenticationChannel = null)
    {
        // By default set the authenticationChannel as the username to update
        if(empty($authenticationChannel)) {
            $authenticationChannel = $usernameOrId;
        }

        // If an username is provided instead of an ID, fetch the linked ID from it 
        if(!is_numeric($usernameOrId)) {
            /** @var Users $userApi */
            $userApi = $this->helix->getService(Users::SERVICE_NAME);
            $usernameOrId = $userApi->getUserId($usernameOrId);
        }

        $parameters = ['broadcaster_id' => $usernameOrId];

        if(empty($newData)) {
            throw new InvalidArgumentException("At least one update parameter must be specified.");
        }
        
        $this->helix->queryWithBody(Client::QUERY_TYPE_PATCH, "/channels", $parameters, $newData, $authenticationChannel);

        return true;
    }

    /**
     * Starts a commercial on the given channel.
     * 
     * @param int|string $usernameOrId The username or ID of the channel broadcaster to start commercials on.
     * @param int $length The commercial length, in seconds. Valid options: 30, 60, 90, 120, 150, 180.
     * @param string|null $authenticationChannel The authentication channel to get the client token from in the
     *                                           token provider.
     * 
     * @return array Information on the started commercial, or false if starting the commercial failed.
     */
    public function startCommercial($usernameOrId, int $length, $authenticationChannel = null)
    {
        // By default set the authenticationChannel as the username to update
        if(empty($authenticationChannel)) {
            $authenticationChannel = $usernameOrId;
        }

        if(!in_array($length, self::VALID_COMMERCIAL_LENGTHS)) {
            throw new InvalidArgumentException("Invalid commercial length.");
        }

        // If an username is provided instead of an ID, fetch the linked ID from it 
        if(!is_numeric($usernameOrId)) {
            /** @var Users $userApi */
            $userApi = $this->helix->getService(Users::SERVICE_NAME);
            $usernameOrId = $userApi->getUserId($usernameOrId);
        }

        $parameters = [
            'broadcaster_id' => $usernameOrId,
            'length' => $length
        ];

        $result = $this->helix->query(Client::QUERY_TYPE_POST, "/channels/commercial", $parameters, $authenticationChannel);

        if(!empty($result)) {
            return reset($result->data);
        }

        return false;
    }
}