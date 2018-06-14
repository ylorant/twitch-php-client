<?php

namespace TwitchClient\API\Kraken\Services;

use TwitchClient\API\Kraken\Kraken;

/**
 * Class Service
 * @package TwitchClient\API\Kraken\Services
 */
abstract class Service
{
    protected $kraken; //< Kraken base object reference

    const SERVICE_NAME = "";

    /**
     * Service constructor.
     * @param Kraken $kraken The Kraken client that will be used to send requests
     */
    public function __construct(Kraken $kraken)
    {
        $this->kraken = $kraken;
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
