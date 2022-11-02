<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Functional\Provider;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Migrations\Data\ORM\LoadWebsiteData as LoadDefaultWebsiteData;
use Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures\LoadWebsiteData;

/**
 * @group CommunityEdition
 */
class WebsiteProviderTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadWebsiteData::class ]);
    }

    public function testGetWebsites()
    {
        $websites = $this->getContainer()->get('oro_website.website.provider')->getWebsites();
        $this->assertCount(1, $websites);
    }

    public function testGetWebsiteIds()
    {
        $websiteIds = $this->getContainer()->get('oro_website.website.provider')->getWebsiteIds();

        $repository = $this->getContainer()
            ->get('doctrine')
            ->getManagerForClass(Website::class)
            ->getRepository(Website::class);

        $expected = $repository->findOneBy(['default' => true]);

        $this->assertEquals([$expected->getId()], $websiteIds);
    }

    public function testGetWebsiteChoices()
    {
        $websiteChoices = $this->getContainer()->get('oro_website.website.provider')->getWebsiteChoices();
        $this->assertCount(1, $websiteChoices);
        $this->assertArrayHasKey(LoadDefaultWebsiteData::DEFAULT_WEBSITE_NAME, $websiteChoices);
    }
}
