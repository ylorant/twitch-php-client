<?php
namespace TwitchClient\API\Helix\Services;

use RuntimeException;
use TwitchClient\Client;


/**
 * Twitch Helix API resource: Videos.
 * Handles videos through the Helix API.
 */
class Videos extends Service
{
    const SERVICE_NAME = "videos";

    const FILTER_ID = "id";
    const FILTER_GAME_ID = "game_id";
    const FILTER_LANGUAGE = "language";
    const FILTER_USER_ID = "user_id";
    const FILTER_USER_LOGIN = "user_login";
    const FILTER_PERIOD = "period";
    const FILTER_ORDER = "order";
    const FILTER_TYPE = "type";

    const FILTERS = [
        self::FILTER_ID,
        self::FILTER_GAME_ID,
        self::FILTER_LANGUAGE,
        self::FILTER_USER_ID,
        self::FILTER_USER_LOGIN,
        self::FILTER_ORDER,
        self::FILTER_PERIOD,
        self::FILTER_TYPE
    ];

    const MANDATORY_FILTERS = [
        self::FILTER_ID, 
        self::FILTER_GAME_ID, 
        self::FILTER_USER_ID
    ];

    /** @var string The cursor for pagination used in the getStreams() method. */
    protected $cursor;

    /**
     * Get info on videos according to the given filters. 
     * 
     * @param array $filters An array containing the filters for the request. At least one of the following filters
     *                       is mandatory :
     *                       - FILTER_ID: Filter by video IDs. Max. 100 IDs (as array).
     *                       - FILTER_GAME_ID: Filter by game ID. One only.
     *                       - FILTER_USER_ID: Filter by user ID. One only.
     *                       There are other optional filters available, if the FILTER_ID parameter is not used :
     *                       - FILTER_LANGUAGE: Filter by language. One only.
     *                       - FILTER_PERIOD: Filter by the period in which the video has been created.
     *                       - FILTER_SORT: Sets the sort order of results.
     *                       - FILTER_TYPE: filter by video type.
     * @return array An array of videos corresponding to the given filters. 
     * 
     * @see https://dev.twitch.tv/docs/api/reference#get-videos
     */
    public function getVideos(array $filters = [], int $length = 20, bool $continue = true)
    {
        $parameters = [
            "after" => $continue ? $this->cursor : null,
            "first" => $length
        ];

        $appliedMandatoryFilters = array_intersect(array_keys($filters), self::MANDATORY_FILTERS);
        if(empty($appliedMandatoryFilters)) {
            throw new RuntimeException("Need to specify at least one parameter");
        }

        // Fill in the filters
        foreach($filters as $filterKey => $filterValue) {
            if(in_array($filterKey, self::FILTERS)) {
                $parameters[$filterKey] = $filterValue;
            }
        }

        $result = $this->helix->query(Client::QUERY_TYPE_GET, "/videos", $parameters);

        // Store the cursor
        if(!empty($result->pagination->cursor)) {
            $this->cursor = $result->pagination->cursor;
        } else {
            $this->cursor = null;
        }

        return $result->data;
    }
}