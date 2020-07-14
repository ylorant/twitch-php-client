<?php
namespace TwitchClient\API\Helix\Services;

use TwitchClient\Client;


/**
 * Twitch Helix API resource: Tags.
 * Handles tag management for a channel status.
 */
class Streams extends Service
{
    const SERVICE_NAME = "streams";

    const DIRECTION_FORWARD = "forward";
    const DIRECTION_BACKWARD = "backward";

    const FILTER_GAME_ID = "game_id";
    const FILTER_LANGUAGE = "language";
    const FILTER_USER_ID = "user_id";
    const FILTER_USER_LOGIN = "user_login";

    const FILTERS = [
        self::FILTER_GAME_ID, 
        self::FILTER_LANGUAGE, 
        self::FILTER_USER_ID, 
        self::FILTER_USER_LOGIN
    ];

    /** @var string The cursor for pagination used in the getStreams() method. */
    protected $cursor;

    /**
     * Gets information about active streams. The order in which the streams are returned are
     * by descending number of viewers. Multiple parameters can be specified to filter the list
     * of streams to be returned, and a cursor-style pagination system is in place to limit the
     * results number.
     * 
     * Considering there are are multiple available filters, parameters for this method are not separated,
     * but compiled into one parameter.
     * 
     * @param array $filters  The list of filters to filter the streams with. Optional. Available parameters:
     *                        - FILTER_GAME_ID: Filter by game ID.
     *                        - FILTER_LANGUAGE: Filter by language.
     *                        - FILTER_USER_ID: Filter by user IDs.
     *                        - FILTER_USER_LOGIN: Filter by user names (login names).
     * @param int   $length   The maximum amount of results to return. Default is 20.
     * @param bool  $continue Whether to continue the previous call independently of the filters. Defaults to true.
     * 
     * @return array An array of streams corresponding to the given filters.
     */
    public function getStreams(array $filters = [], int $length = 20, bool $continue = true)
    {
        $parameters = [
            "after" => $continue ? $this->cursor : null,
            "first" => $length
        ];

        // Fill in the filters
        foreach($filters as $filterKey => $filterValue) {
            if(in_array($filterKey, self::FILTERS)) {
                $parameters[$filterKey] = $filterValue;
            }
        }

        $result = $this->helix->query(Client::QUERY_TYPE_GET, "/streams", $parameters);

        // Store the cursor
        if(!empty($result->pagination->cursor)) {
            $this->cursor = $result->pagination->cursor;
        } else {
            $this->cursor = null;
        }

        return $result->data;
    }
}