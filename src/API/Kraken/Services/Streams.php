<?php
namespace TwitchClient\API\Kraken\Services;

use TwitchClient\Client;

class Streams extends Service
{
    const SERVICE_NAME = "streams";

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

        $channelInfo = $reply->channel;

        return $reply->stream;
    }
}