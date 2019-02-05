<?php
namespace TwitchClient\API\Helix\Services;

use TwitchClient\API\Helix\Helix;

/**
 * Class Service
 * @package TwitchClient\API\Helix\Services
 */
abstract class Service
{
    protected $helix; //< Helix base object reference

    const SERVICE_NAME = "";

    /**
     * Service constructor.
     * @param Helix $helix The Helix client that will be used to send requests
     */
    public function __construct(Helix $helix)
    {
        $this->helix = $helix;
    }

    /**
     * Returns the service's name from the defined constants in subclasses.
     *
     * @return string The service's name
     */
    public static function getServiceName()
    {
        return static::SERVICE_NAME;
    }
}
