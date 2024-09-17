<?php
namespace TwitchClient\API\Helix\Services;

use TwitchClient\Client;

class Whispers extends Service
{
    const SERVICE_NAME = "whispers";
    const SCOPES = ['user:manage:whispers'];

    /**
     * Sends a whisper message to an user.
     * 
     * @param string|int  $sourceLoginOrId       The username or ID to send the whisper as. If possible, the access 
     *                                           token for the specified user will be used for the request.
     * @param string|int  $targetLoginOrId       The username or ID to send the whisper to.
     * @param string      $message               The message to send. 
     * @param string|null $authenticationChannel The authentication channel to get the client token from in the token
     *                                           provider.
     * 
     * @see https://dev.twitch.tv/docs/api/reference/#send-whisper
     */
    public function sendWhisper($sourceLoginOrId, $targetLoginOrId, $message, $authenticationChannel = null)
    {
        /** @var Users $userApi */
        $userApi = $this->helix->getService(Users::SERVICE_NAME);

        if (!is_numeric($sourceLoginOrId)) {
            if (is_null($authenticationChannel)) {
                $authenticationChannel = $sourceLoginOrId;
            }

            $sourceLoginOrId = $userApi->getUserId($sourceLoginOrId);
        }

        if (!is_numeric($targetLoginOrId)) {
            $targetLoginOrId = $userApi->getUserId($targetLoginOrId);
        }

        $queryParameters = [
            'from_user_id' => $sourceLoginOrId,
            'to_user_id' => $targetLoginOrId
        ];

        $bodyParameters = [
            'message' => $message
        ];

        $result = $this->helix->queryWithBody(
            Client::QUERY_TYPE_POST,
            "/whispers",
            $queryParameters,
            $bodyParameters,
            $authenticationChannel
        );

        if(!$result) {
            return false;
        }

        return true;
    }
}