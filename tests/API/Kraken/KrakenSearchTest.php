<?php
namespace TwitchClient\Tests\API\Kraken;

use PHPUnit\Framework\TestCase;
use TwitchClient\API\Kraken\Kraken;
use stdClass;
use Faker;
use TwitchClient\Tests\LoadConfigTrait;

class KrakenSearchTest extends TestCase
{
    use LoadConfigTrait;

    /**
     * Tests searching for a game
     */
    public function testSearchGame()
    {
        $kraken = new Kraken(self::$tokenProvider);

        $games = $kraken->search->games('Pokemon White 2');
        
        $this->assertNotEmpty($games);
        $this->assertCount(1, $games);
        $this->assertEquals("Pokémon Black/White Version 2", $games[0]->name);
    }
}