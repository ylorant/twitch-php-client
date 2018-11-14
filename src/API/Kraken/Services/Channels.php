<?php
namespace TwitchClient\API\Kraken\Services;

use TwitchClient\API\Kraken\Kraken;
use stdClass;
use DateTime;
use TwitchClient\Client;

/**
 * Twitch API service: channels info handler.
 */
class Channels extends Service
{
    const SERVICE_NAME = "channels";

    /**
     * Fetch informations for a given channel.
     * Basically executes the API call to get channel data from Twitch and returns it.
     *
     * @param string|int|null $channel The channel name, ID or NULL to get the info of the channel tied to the 
     *                                 token ID given as default.
     * @return stdClass|bool Available information as stdClass or false if the channel is not found.
     *
     * @see https://dev.twitch.tv/docs/v5/reference/channels/#get-channel-by-id
     */
    public function info($channel = null)
    {
        if (!empty($channel) && !is_numeric($channel)) {
            $channel = $this->kraken->getService('users')->getUserId($channel);

            if(is_null($channel)) {
                return false;
            }
        }

        return $this->kraken->query(Client::QUERY_TYPE_GET, $channel ? "/channels/$channel" : "/channel");
    }

    /**
     * Fetch the list of followers for a channel.
     * Since Twitch's API doesn't allow to get the whole list of followers in one go,
     * the limitation is present there too. Also, since Twitch supports cursor-based pagination instead of regular one,
     * you'll have to get each page one by one (the cursor for the next page is given in each result).
     *
     * @param string|int $channel The channel name or ID.
     * @param array $parameters The parameters for the list to retrieve. No parameter is mandatory.
     *                          Available parameters:
     *                            - limit: The limit for the element count in the list.
     *                                     Follows Twitch's limit of maximum 100 followers.
     *                            - start; Where to start from. Expects a cursor, refer to the Twitch API for more info.
     *                            - order: The order in which the results will be presented, desc or asc.
     *                                     Order will be by follow time.
     *                            - detailed info: boolean indicating whether to get extended info
     *                                             for each user or only the nickname.
     * @return object|bool An object containing the resulting list, along with other useful data (count, cursor).
     *                     If detailed info is requested, then each user is listed in an object.
     *                     If not, only the nickname as a string will be returned in the list.
     *                     If the user doesn't exist, it will return false.
     *
     * @see https://dev.twitch.tv/docs/v5/reference/channels/#get-channel-followers
     */
    public function followers($channel, array $parameters = [])
    {
        $queryParameters = array(
            'limit' => !empty($parameters['limit']) ? $parameters['limit'] : '',
            'cursor' => !empty($parameters['start']) ? $parameters['start'] : '',
            'direction' => !empty($parameters['order']) ? $parameters['order'] : '',
        );

        $queryParameters = array_filter($queryParameters);

        if(!is_numeric($channel)) {
            $channel = $this->kraken->getService('users')->getUserId($channel);
        }

        $userList = $this->kraken->query(Client::QUERY_TYPE_GET, "/channels/$channel/follows");

        return $userList;
    }

    /**
     * Updates a channel's data. This method requires to have a valid access token, that can update a channel of course.
     *
     * @param string|int $channel The name or ID of the channel to update.
     * @param array $parameters The parameters to update. Available parameters:
     *                    - status: The channel title
     *                    - game: The channel game
     *                    - delay: The channel delay, in seconds.
     *                             It requires the access token to be one from the channel owner.
     *                    - channel_feed-enabled: Set to true to enable the channel feed.
     *                                            Requires an access token from the channel owner.
     * @return mixed The reply from the API, which is the updated status for the channel. If it doesn't work, it returns
     *               false.
     * 
     * @see https://dev.twitch.tv/docs/v5/reference/channels/#update-channel
     */
    public function update($channel, array $parameters = [])
    {
        $queryParameters = [
            "channel" => [
                'status' => $parameters['status'] ?? null,
                'game' => $parameters['game'] ?? null,
                'delay' => $parameters['delay'] ?? null,
                'channel_feed_enabled' => $parameters['channel_feed_enabled'] ?? null
            ]
        ];

        $queryParameters["channel"] = array_filter($queryParameters["channel"]);
        $channelId = $channel;

        if(!is_numeric($channel)) {
            $channelId = $this->kraken->getService('users')->getUserId($channel);
        }

        return $this->kraken->query(Client::QUERY_TYPE_PUT, "/channels/$channelId", $queryParameters, $channel);
    }
}
