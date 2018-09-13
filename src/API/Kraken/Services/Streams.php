<?php
namespace TwitchClient\API\Kraken\Services;

use TwitchClient\Client;

class Streams extends Service
{
    const SERVICE_NAME = "streams";

    const STREAM_TYPE_LIVE = "live";
    const STREAM_TYPE_PLAYLIST = "playlist";
    const STREAM_TYPE_ALL = "all";

    /**
     * Gets the stream information for a specific user. If the user is not streaming, this will return null. If the user
     * is streaming, it will return the stream object, containing the stream info. When needed, this method can also
     * return the channel info in the second parameter as a reference, enabling to return both stream and channel info
     * in one API call.
     * 
     * @param string|int $user The user ID or name to fetch the stream info of.
     * @param mixed $channelInfo A reference to store the channel info in if needed. Optional.
     * 
     * @return object|null The stream info if found, null if not.
     */
    public function info($user, &$channelInfo = null)
    {
        if (!is_numeric($user)) {
            $user = $this->kraken->getService('users')->getUserId($user);
        }

        $reply = $this->kraken->query(Client::QUERY_TYPE_GET, "/streams/$user");

        if(empty($reply->stream)) {
            return null;
        }

        $channelInfo = $reply->stream->channel;

        return $reply->stream;
    }

    /**
     * Gets a list of currently live streams.
     * 
     * @return object The stream list. It can return an empty object if there is no stream available, but on Twitch it
     *                would rarely be the case.
     */
    public function list(array $parameters = [])
    {
        $queryParameters = [
            "channel" => !empty($parameters["channel"]) ? implode(',', (array) $parameters["channel"]) : null,
            "game" => $parameters["game"] ?? null,
            "language" => $parameters["language"] ?? null,
            "stream_type" => $parameters["stream_type"] ?? null,
            "limit" => $parameters["limit"] ?? null,
            "offset" => $parameters["offset"] ?? null
        ];

        $queryParameters = array_filter($queryParameters);

        $streamList = $this->kraken->query(Client::QUERY_TYPE_GET, "/streams/", $queryParameters);

        return $streamList->streams;
    }

    /**
     * Gets the global summary of streams on Twitch (or on a specific game on Twitch). It returns basically two vars :
     * the total number of streams and the total number of viewers.
     * 
     * @param string $game The game to limit the search to.
     * 
     * @return array The statistics for the searched games or for all streams on Twitch.
     */
    public function summary($game = null)
    {
        $queryParameters = [];

        if(!empty($game)) {
            $queryParameters['game'] = $game;
        }

        return $this->kraken->query(Client::QUERY_TYPE_GET, "/streams/summary", $queryParameters);
    }
}