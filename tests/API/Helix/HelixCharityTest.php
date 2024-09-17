<?php
namespace TwitchClient\Tests\API\Helix;

use PHPUnit\Framework\TestCase;
use stdClass;
use TwitchClient\API\Helix\Helix;
use TwitchClient\Tests\LoadConfigTrait;

class HelixCharityTest extends TestCase
{
    use LoadConfigTrait;

    public function testGetCampaign()
    {
        $helix = new Helix(self::$tokenProvider);
        $campaignInfo = $helix->charity->campaign(ACCESS_CHANNEL);
        
        $this->assertNotNull($campaignInfo);
        $this->assertInstanceOf(stdClass::class, $campaignInfo);
        $this->assertEquals(ACCESS_CHANNEL, $campaignInfo->broadcaster_name);

        return $campaignInfo->id;
    }

    public function testGetCampaignNotAllowed()
    {
        $helix = new Helix(self::$tokenProvider);
        $campaignInfo = $helix->charity->campaign(EMPTY_CHANNEL, ACCESS_CHANNEL);

        $this->assertFalse($campaignInfo);
    }

    /**
     * @depends testGetCampaign
     */
    public function testGetDonations($campaignId)
    {

        $helix = new Helix(self::$tokenProvider);
        $donations = $helix->charity->donations(ACCESS_CHANNEL);
        
        $this->assertNotNull($donations);
        $this->assertIsArray($donations);
        
        if(count($donations) > 0) {
            $sampleDonation = reset($donations);
            $this->assertEquals($campaignId, $sampleDonation->campaign_id);
        }
    }
}