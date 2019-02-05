<?php
namespace TwitchClient\API\Helix\Services;

use TwitchClient\Client;


/**
 * Twitch Helix API resource: Tags.
 * Handles tag management for a channel status.
 */
class Tags extends Service
{
    const SERVICE_NAME = "tags";

    /** @var string The cursor for pagination used in the getTags() method. */
    protected $cursor;

    /**
     * Gets tags from the global tag list on Twitch. Tag IDs can be specified to precise the search.
     * 
     * @param array $tagIds The IDs of the specific tags to get.
     * @param int $length The number of items to be returned.
     * @param bool $continue Set this to true to continue the last search, using the embedded cursor. Defaults to true.
     * 
     * @return array The list of found tags. If a multi-page result is needed (the API will reply with a pagination cursor),
     *               then it will be stored internally.
     * 
     * @see https://dev.twitch.tv/docs/api/reference/#get-all-stream-tags
     */
    public function getTags(array $tagIds = [], $length = 20, bool $continue = true)
    {
        $parameters = [
            "after" => $continue ? $this->cursor : null,
            "first" => $length,
            "tag_id" => $tagIds
        ];

        $result = $this->helix->query(Client::QUERY_TYPE_GET, "/tags/streams", $parameters);

        // Store the cursor if needed
        if(!empty($result->pagination->cursor)) {
            $this->cursor = $result->pagination->cursor;
        } else {
            $this->cursor = null;
        }

        return $result->data;
    }

    /**
     * Checks if there is a cursor available to continue a previous getTags() search.
     * 
     * @return bool True if there is a cursor available and there is more results to fetch, false if not.
     */
    public function hasMoreTags()
    {
        return !empty($this->cursor);
    }

    /**
     * Gets the tags for a specific stream/broadcaster.
     * 
     * @param string|int $broadcaster The broadcaster ID or name. If the name is specified, then a query to get the broadcaster ID
     *                                from its login will be made beforehand.
     * 
     * @return array|bool The list of tags for the given stream if found, false if not.
     * 
     * @see https://dev.twitch.tv/docs/api/reference/#get-stream-tags
     */
    public function getStreamTags($broadcaster)
    {
        if (!is_numeric($broadcaster)) {
            $broadcaster = $this->helix->getService('users')->getUserId($broadcaster);

            if(empty($broadcaster)) {
                return false;
            }
        }

        $result = $this->helix->query(Client::QUERY_TYPE_GET, "/streams/tags", ["broadcaster_id" => $broadcaster]);

        if(!empty($result->data)) {
            return $result->data;
        }

        return false;
    }

    /**
     * Updates the tags for the stream of a given broadcaster.
     * 
     * @param string $broadcaster The ID or the username of the broadcaster to update the stream of.
     * @param array $tags The tag IDs to set.
     * 
     * @return bool True if the tags updated, false if an error occured.
     * 
     * @see https://dev.twitch.tv/docs/api/reference/#replace-stream-tags
     */
    public function updateStreamTags($broadcaster, array $tags)
    {
        if (!is_numeric($broadcaster)) {
            $broadcaster = $this->helix->getService('users')->getUserId($broadcaster);

            if(empty($broadcaster)) {
                return false;
            }
        }

        $result = $this->helix->query(Client::QUERY_TYPE_PUT, "/streams/tags?broadcaster_id=". $broadcaster, ["tag_ids" => $tags]);
    }
}