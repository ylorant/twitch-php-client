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

    /** @var string The cursor for pagination used in the getStreams() method. */
    protected $cursor;

    /**
     * Gets information about active streams. The order in which the streams are returned are
     * by descending number of viewers. Multiple parameters can be specified to filter the list
     * of streams to be returned, and a cursor-style pagination system is in place to limit the
     * results number.
     * 
     */
    public function getStreams()
    {

    }
}