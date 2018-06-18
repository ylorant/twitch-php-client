<?php
namespace TwitchClient\Log;

use Psr\Log\AbstractLogger;

/**
 * Dummy logger. Will not log anything.
 */
class DummyLogger extends AbstractLogger
{
    public function log($level, $message, array $context = array())
    {
        return;
    }
}