<?php
namespace TwitchClient\Tests\API\Helix;

use PHPUnit\Framework\TestCase;
use TwitchClient\API\Helix\Helix;
use TwitchClient\Tests\LoadConfigTrait;
use Faker\Factory as FakerFactory;
use stdClass;

class HelixSearchTest extends TestCase
{
    use LoadConfigTrait;

    public function testSearchCategories()
    {
        $helix = new Helix(self::$tokenProvider);
        $searchResults = $helix->search->categories("Super Mario 64");

        $this->assertIsArray($searchResults);

        $firstElement = reset($searchResults);
        $this->assertInstanceOf(stdClass::class, $firstElement);
        $this->assertObjectHasAttribute("id", $firstElement);
        $this->assertObjectHasAttribute("name", $firstElement);
        $this->assertObjectHasAttribute("box_art_url", $firstElement);
    }

    public function testSearchCategoriesWithoutResult()
    {
        $faker = FakerFactory::create();

        $helix = new Helix(self::$tokenProvider);
        $searchResults = $helix->search->categories($faker->md5());

        $this->assertIsArray($searchResults);
        $this->assertCount(0, $searchResults);
    }
}