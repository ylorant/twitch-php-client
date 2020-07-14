<?php
namespace TwitchClient\Tests\API\Helix;

use PHPUnit\Framework\TestCase;
use stdClass;
use TwitchClient\API\Helix\Helix;
use TwitchClient\API\Helix\Services\Streams;
use TwitchClient\Tests\ExtraAssertionsTrait;
use TwitchClient\Tests\LoadConfigTrait;

class HelixTagsTest extends TestCase
{
    use LoadConfigTrait;
    use ExtraAssertionsTrait;

    const STREAM_OBJECT_KEYS = [
        'id',
        'user_id',
        'user_name',
        'game_id',
        'type',
        'title',
        'viewer_count',
        'started_at',
        'language',
        'thumbnail_url',
        'tag_ids',
    ];

    /**
     * Tests getting a stream list from the Helix API.
     * TODO: More tests to check for non-existing streams and stuff.
     */
    public function testGetStreams()
    {
        $helix = new Helix(self::$tokenProvider);
        /** @var Streams $streamsApi */
        $streamsApi = $helix->streams;
        
        // Tests getting streams without any filter
        $streams = $streamsApi->getStreams();
        $sampleStream = reset($streams);
        $this->assertInternalType('array', $streams);
        $this->assertInstanceOf(stdClass::class, $sampleStream);
        $this->assertObjectHasAttributes(self::STREAM_OBJECT_KEYS, $sampleStream);

        // Tries getting streams with a filter
        $streams = $streamsApi->getStreams([Streams::FILTER_LANGUAGE => "fr"], 20, false);
        $sampleStream = reset($streams);
        $this->assertInternalType('array', $streams);
        $this->assertInstanceOf(stdClass::class, $sampleStream);
        $this->assertObjectHasAttributes(self::STREAM_OBJECT_KEYS, $sampleStream);
        $this->assertEquals('fr', $sampleStream->language);
    }
}