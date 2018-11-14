<?php
namespace TwitchClient\API\Kraken\Services;

use TwitchClient\Client;

class Search extends Service
{
    const SERVICE_NAME = "search";

    /**
     * Search for a game by its name or part of its name.
     * 
     * @param string $search The terms to look for in the game name.
     * @param bool   $live   Set this to true if you want to only get results for games that have at least one channel
     *                       currently streaming them.
     * 
     * @return array The list of found games as an array of objects (each one containing the game info).
     * 
     * @see https://dev.twitch.tv/docs/v5/reference/search/#search-games
     */
    public function games($search, $live = false)
    {
        $queryParameters = [
            'query' => $search,
            'live' => $live
        ];

        $result = $this->kraken->query(Client::QUERY_TYPE_GET, '/search/games', $queryParameters);

        return $result->games ?? [];
    }
}