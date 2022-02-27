<?php
namespace TwitchClient\API\Helix\Services;

use TwitchClient\Client;

/**
 * Twitch Helix API resource: Search.
 * Allows to search resources on Twitch directory.
 */
class Search extends Service
{
    const SERVICE_NAME = "search";

    /** @var string The cursor for pagination used in the categories() method. */
    protected $categoriesCursor;

    /**
     * Search for categories ("games").
     * 
     * @param string $search The category name to look for.
     * @param int $length The number of items to be returned.
     * @param bool $continue Set this to true to continue the last search, using the embedded cursor. Defaults to true.
     * 
     * @return array An array containing all the matching results.
     * 
     * @see https://dev.twitch.tv/docs/api/reference#search-categories
     */
    public function categories(string $search, $length = 20, bool $continue = true)
    {
        $parameters = [
            "after" => $continue ? $this->categoriesCursor : null,
            "first" => $length,
            "query" => urlencode($search)
        ];

        $result = $this->helix->query(Client::QUERY_TYPE_GET, "/search/categories", $parameters);

        // Store the cursor if needed
        if(!empty($result->pagination->cursor)) {
            $this->categoriesCursor = $result->pagination->cursor;
        } else {
            $this->categoriesCursor = null;
        }

        return $result->data;
    }
}