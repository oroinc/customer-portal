<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures\LoadWebsiteData;

class WebsiteRepositoryTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadWebsiteData::class]);
    }

    /**
     * @dataProvider getAllWebsitesProvider
     */
    public function testGetAllWebsites(array $expectedData)
    {
        $websites = $this->getRepository()->getAllWebsites();
        foreach ($websites as $key => $website) {
            self::assertEquals($key, $website->getId());
        }
        $websites = array_map(
            function (Website $website) {
                return $website->getName();
            },
            $websites
        );
        self::assertEquals($expectedData, array_values($websites));
    }

    public function getAllWebsitesProvider(): array
    {
        return [
            [
                'expected' => [
                    'Default',
                    'US',
                    'Canada',
                    'CA'
                ],
            ],
        ];
    }

    public function testGetAllWebsitesIds(): void
    {
        $websiteDefault = $this->getRepository()->findOneBy(['default' => true]);
        $website1 = $this->getReference(LoadWebsiteData::WEBSITE1);
        $website2 = $this->getReference(LoadWebsiteData::WEBSITE2);
        $website3 = $this->getReference(LoadWebsiteData::WEBSITE3);

        $expectedIds = array_map(
            static fn (Website $website) => $website->getId(),
            [$websiteDefault, $website1, $website2, $website3]
        );
        sort($expectedIds);

        $websitesIds = $this->getRepository()->getAllWebsitesIds($website1->getOrganization());
        sort($websitesIds);

        $this->assertEquals($expectedIds, $websitesIds);

        $websitesIds = $this->getRepository()->getAllWebsitesIds();
        sort($websitesIds);

        $this->assertEquals($expectedIds, $websitesIds);
    }

    public function testGetDefaultWebsite()
    {
        $defaultWebsite = $this->getRepository()->getDefaultWebsite();
        $this->assertEquals('Default', $defaultWebsite->getName());
    }

    /**
     * @dataProvider getAllWebsitesProvider
     */
    public function testBatchIterator(array $expectedWebsiteNames)
    {
        $websitesIterator = $this->getRepository()->getBatchIterator();

        $websiteNames = [];
        foreach ($websitesIterator as $website) {
            $websiteNames[] = $website->getName();
        }

        $this->assertEquals($expectedWebsiteNames, $websiteNames);
    }

    /**
     * @dataProvider getAllWebsitesProvider
     */
    public function testGetWebsiteIdentifiers(array $websites)
    {
        $websiteIds = array_map(
            function ($websiteReference) {
                return 'Default' === $websiteReference
                    ? $this->getRepository()->getDefaultWebsite()->getId()
                    : $this->getReference($websiteReference)->getId();
            },
            $websites
        );
        $this->assertEqualsCanonicalizing($websiteIds, $this->getRepository()->getWebsiteIdentifiers());
    }

    public function testCheckWebsiteExists()
    {
        $website = $this->getReference(LoadWebsiteData::WEBSITE1);
        $result = $this->getRepository()->checkWebsiteExists($website->getId());
        $this->assertNotEmpty($result);
    }

    private function getRepository(): WebsiteRepository
    {
        return self::getContainer()->get('doctrine')->getRepository(Website::class);
    }
}
