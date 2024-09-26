<?php
namespace TwitchClient\API\Helix\Services;

use TwitchClient\Client;

/**
 * Twitch Helix API resource: Chat.
 * Handles chat-related operations.
 */
class Chat extends Service
{
    const SERVICE_NAME = "chat";
    const SCOPES = ["moderator:manage:shoutouts"];

    /**
     * Sends a shoutout on the given broadcaster chat to another broadcaster.
     * 
     * @param mixed $sourceLoginOrId The source broadcaster sending the shoutout (it will show on their chat).
     * @param mixed $targetLoginOrId The target broadcaster to shout out.
     * @param mixed $senderLoginOrId An username or id to use as "sender". Must correspond to the token used for the 
     *                               query. If not provided, the source login will be used.
     * @param mixed $authenticationTarget The target token to use. If not provided, the sender login will be used.
     * @return bool True if the command was sent successfully, false if not.
     */
    public function shoutout($sourceLoginOrId, $targetLoginOrId, $senderLoginOrId = null, $authenticationTarget = null)
    {
        /** @var Users $userApi */
        $userApi = $this->helix->getService(Users::SERVICE_NAME);

        // By default set the authenticationChannel as the sender login
        if (empty($authenticationTarget)) {
            $authenticationTarget = $senderLoginOrId ?? $sourceLoginOrId;
        }

        // Resolve logins
        if (!is_numeric($sourceLoginOrId)) {
            $sourceLoginOrId = $userApi->getUserId($sourceLoginOrId);
        }

        if (!is_numeric($targetLoginOrId)) {
            $targetLoginOrId = $userApi->getUserId($targetLoginOrId);
        }

        // Set the default sender account to the source broadcaster if not present
        if (empty($senderLoginOrId)) {
            $senderLoginOrId = $sourceLoginOrId;
        } elseif (!is_numeric($senderLoginOrId)) {
            $senderLoginOrId = $userApi->getUserId($senderLoginOrId);
        }

        $queryParameters = [
            'from_broadcaster_id' => $sourceLoginOrId,
            'to_broadcaster_id' => $targetLoginOrId,
            'moderator_id' => $senderLoginOrId
        ];

        $result = $this->helix->query(Client::QUERY_TYPE_POST, "/chat/shoutouts", $queryParameters, $authenticationTarget);

        if(!$result) {
            return false;
        }

        return true;
    }
}