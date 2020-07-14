<?php
namespace TwitchClient\Tests\API\Helix;

use PHPUnit\Framework\TestCase;
use TwitchClient\API\Helix\Helix;
use TwitchClient\Tests\LoadConfigTrait;

class HelixTagsTest extends TestCase
{
    use LoadConfigTrait;

    /**
     * Tests getting a tag list from the Helix API.
     */
    public function testGetTags()
    {
        $helix = new Helix(self::$tokenProvider);
        $tagsApi = $helix->tags;
        $tags = $tagsApi->getTags();
        
        $this->assertNotNull($tags);
        $this->assertInternalType("array", $tags);

        // Twitch should have more than 20 available tags, so we check that the cursor is available
        $hasMoreResults = $tagsApi->hasMoreTags();
        $this->assertTrue($hasMoreResults);

        // Try to get a specific tag by its ID, using the previous results to get the ID.
        $firstTag = reset($tags);
        $onlyTag = $tagsApi->getTags([$firstTag->tag_id]);

        $this->assertCount(1, $onlyTag);
        $this->assertEquals($firstTag, reset($onlyTag));

        return $firstTag->tag_id;
    }

    /**
     * Tests getting the wrong stream tags.
     */
    public function testGetWrongStreamTags()
    {
        $helix = new Helix(self::$tokenProvider);
        $tags = $helix->tags->getStreamTags("NonExistentUser". uniqid());
        $this->assertFalse($tags);

        
        $tags = $helix->tags->getStreamTags(-1);
        $this->assertFalse($tags);
    }

    /**
     * Tests updating a stream's tags.
     * 
     * @depends testGetTags
     */
    public function testUpdateStreamTags($tagId)
    {
        $helix = new Helix(self::$tokenProvider);
        $helix->setDefaultTarget(ACCESS_CHANNEL);
        
        $updated = $helix->tags->updateStreamTags(ACCESS_CHANNEL, [$tagId]);
        $this->assertNull($updated);

        $tags = $helix->tags->getStreamTags(ACCESS_CHANNEL);

        $this->assertInternalType('array', $tags);
        
        $firstTag = reset($tags);
        $this->assertEquals($tagId, $firstTag->tag_id);
 
        return $tagId;
    }

    /**
     * Tests updating a wrong username.
     * 
     * @depends testGetTags
     */
    public function testUpdateWrongUser($tagId)
    {
        $helix = new Helix(self::$tokenProvider);
        $helix->setDefaultTarget(ACCESS_CHANNEL);
        
        $updated = $helix->tags->updateStreamTags("NonExistentUser". uniqid(), [$tagId]);
        $this->assertFalse($updated);
    }
}