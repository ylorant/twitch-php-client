<?php
namespace TwitchClient\API\Helix\Services;

use TwitchClient\Client;

/**
 * Twitch Helix API resource: Charity.
 * Handles charity info fetching.
 */
class Charity extends Service
{
    const SERVICE_NAME = "charity";
    const SCOPES = ['channel:read:charity'];

    /** @var string The cursor for pagination used in the getDonations() method. */
    protected $cursor;

    /**
     * Gets current campaign info for a given channel. 
     * 
     * @param int|string $usernamesOrId The ID or username of the user to get the campaign from.
     * @param string|null $authenticationChannel The authentication channel to get the client token from in the
     *                                           token provider.
     * @return array An array containing info on the fetched campaign.
     * 
     * @see https://dev.twitch.tv/docs/api/reference/#get-charity-campaign
     */
    public function campaign($usernameOrId, $authenticationChannel = null)
    {
        // By default set the authenticationChannel as the username to update
        if(empty($authenticationChannel)) {
            $authenticationChannel = $usernameOrId;
        }

        // Handle usernames to user ID conversions
        if(!is_numeric($usernameOrId)) {
            /** @var Users $userApi */
            $userApi = $this->helix->getService(Users::SERVICE_NAME);
            $usernameOrId = $userApi->getUserId($usernameOrId);
        }

        $parameters = ['broadcaster_id' => $usernameOrId];
        $result = $this->helix->query(Client::QUERY_TYPE_GET, "/charity/campaigns", $parameters, $authenticationChannel);

        if(!$result) {
            return false;
        }

        if(count($result->data) > 0) {
            return $result->data[0];
        } else {
            return null;
        }
    }

    /**
     * Gets the list of donations related to the charity campaign for a given channel.
     * 
     * @param int|string $usernamesOrId The ID or username of the user to get the campaign from.
     * @param int $length The number of items to fetch. Defaults to 20.
     * @param bool $continue Whether to continue fetching a list previously fetched or not using the stored cursor. 
     *                       Defaults to true.
     * @param string|null $authenticationChannel The authentication channel to get the client token from in the
     *                                           token provider.
     * 
     * @see https://dev.twitch.tv/docs/api/reference/#get-charity-campaign-donations
     */
    public function donations($usernameOrId, int $length = 20, bool $continue = true, $authenticationChannel = null)
    {
        // By default set the authenticationChannel as the username to update
        if(empty($authenticationChannel)) {
            $authenticationChannel = $usernameOrId;
        }

        // Handle usernames to user ID conversions
        if(!is_numeric($usernameOrId)) {
            /** @var Users $userApi */
            $userApi = $this->helix->getService(Users::SERVICE_NAME);
            $usernameOrId = $userApi->getUserId($usernameOrId);
        }

        $parameters = [
            "broadcaster_id" => $usernameOrId,
            "after" => $continue ? $this->cursor : null,
            "first" => $length
        ];

        $result = $this->helix->query(Client::QUERY_TYPE_GET, "/charity/donations", $parameters, $authenticationChannel);

        // Store the cursor
        if(!empty($result->pagination->cursor)) {
            $this->cursor = $result->pagination->cursor;
        } else {
            $this->cursor = null;
        }

        return $result->data;
    }

    /**
     * Sets the fetch cursor for donations list.
     *
     * @param string $cursor The cursor to set.
     * 
     * @return self
     */ 
    public function setCursor(string $cursor)
    {
        $this->cursor = $cursor;

        return $this;
    }
}