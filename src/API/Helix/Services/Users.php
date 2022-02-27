<?php
namespace TwitchClient\API\Helix\Services;

use TwitchClient\Client;


/**
 * Twitch Helix API resource: Users.
 * Handles users through the Helix API. A cache is made of the login/ID relation for each fetched user to avoid multiple queries when
 * needing to fetch user IDs multiple times.
 */
class Users extends Service
{
    const SERVICE_NAME = "users";

    protected static $userIdCache = [];

    /**
     * Gets info about multiple users. Users can be specified either by their IDs or their logins.
     * 
     * @param array $loginsOrIds The logins and/or user IDs to get the info of.
     * 
     * @return array An associative array of the fetched user data, using the ID of the user as key.
     * 
     * @see https://dev.twitch.tv/docs/api/reference/#get-users
     */
    public function getUsers(array $loginsOrIds)
    {
        $parameters = ["login" => [], "id" => []];

        // Separate logins and IDs
        if(!empty($loginsOrIds)) {
            $parameters["id"] = array_filter($loginsOrIds, "is_numeric");
            $parameters["login"] = array_diff($loginsOrIds, $parameters['id']);
        }

        // Filter the parameters to not include empty requests
        $parameters = array_filter($parameters);

        // Query for every ID and login, as now they don't collide with each other
        $result = $this->helix->query(Client::QUERY_TYPE_GET, "/users", $parameters);
        $userData = [];

        // Caching fetched user IDs for later use
        foreach($result->data as $user) {
            self::$userIdCache[$user->login] = $user->id;
            $userData[$user->id] = $user;
        }

        return $userData;
    }

    /**
     * Gets info on a specific user. User can be specified either by their ID or their login.
     * 
     * @param mixed $loginOrId The login or the user ID to get the info of.
     * @return object An object containing the user's data.
     * 
     * @see https://dev.twitch.tv/docs/api/reference#get-users
     */
    public function getUser($loginOrId = null)
    {
        $user = $this->getUsers(!is_null($loginOrId) ? [$loginOrId] : []);
        return reset($user);
    }

    /**
     * Gets the user IDs either from the local cache or by querying them from Twitch's API directly.
     * 
     * @param array $logins The logins to get the IDs of.
     * 
     * @return array The user IDs linked to those logins, in an associative array.
     */
    public function getUsersIds(array $logins)
    {
        $ids = [];
        $idsToFetch = [];

        // Get the already fetched IDs from cache and mark the others for fetching
        foreach($logins as $login) {
            if(isset(self::$userIdCache[$login])) {
                $ids[$login] = self::$userIdCache[$login];
            } else {
                $idsToFetch[] = $login;
            }
        }

        // Fetch needed user IDs
        if(!empty($idsToFetch)) {
            $fetchedIds = $this->fetchUsersIds($idsToFetch);
            $ids = array_merge($ids, $fetchedIds);
        }

        return $ids;
    }

    /**
     * Gets an user ID from its login. This is just a shortcut for Users::getUsersIds() but with only one element.
     * 
     * @param string $login The login to get the ID of.
     * 
     * @return string|bool The user ID that belongs to the login, or false if not found.
     */
    public function getUserId($login)
    {
        $id = $this->getUsersIds([$login]);
        return reset($id);
    }

    /**
     * Gets the user IDs for the given logins and add them to the cache. This method will always call the API,
     * if you're looking to use the cache just to get the user IDs, use the Users::getUsersIds() method.
     * 
     * @param array $logins The logins to fetch the ID of.
     * 
     * @return array The list of fetched IDs, indexed by the user name.
     * 
     * @see https://dev.twitch.tv/docs/api/reference/#get-users
     */
    public function fetchUsersIds(array $logins)
    {
        $result = $this->helix->query(Client::QUERY_TYPE_GET, "/users", ["login" => $logins]);
        $ids = [];

        foreach($result->data as $user) {
            self::$userIdCache[$user->login] = $user->id;
            $ids[$user->login] = $user->id;
        }

        return $ids;
    }

    /**
     * Fetches an user ID from its login. Basically a one-item call for Users::fetchUserIds().
     * 
     * @param string $login The login to fetch the ID of.
     * 
     * @return string The associated user ID.
     * 
     * @see Users::fetchUsersIds()
     */
    public function fetchUserId($login)
    {
        $ids = $this->fetchUsersIds([$login]);
        return reset($ids);
    }
    
    /**
     * Empties the user ID cache. If some time the using code needs to empty the user cache database (for example after an
     * username update or something), this method will cleanup all the users from the ID cache, and then following calls
     * to user methods will refill the cache.
     * 
     * @return void
     */
    public function emptyIdCache()
    {
        self::$userIdCache = [];
    }
}