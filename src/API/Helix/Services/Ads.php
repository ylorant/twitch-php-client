<?php
namespace TwitchClient\API\Helix\Services;

use InvalidArgumentException;
use TwitchClient\Client;

class Ads extends Service
{
    const SERVICE_NAME = "ads";
    const SCOPES = ['channel:edit:commercial'];

    const VALID_COMMERCIAL_LENGTHS = [30, 60, 90, 120, 150, 180];
    
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