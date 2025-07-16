<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Provider;

use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScopeCriteriaProviderTest extends TestCase
{
    private WebsiteManager&MockObject $websiteManager;
    private ScopeCriteriaProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->provider = new ScopeCriteriaProvider($this->websiteManager);
    }

    public function testGetCriteriaField(): void
    {
        $this->assertEquals(ScopeCriteriaProvider::WEBSITE, $this->provider->getCriteriaField());
    }

    public function testGetCriteriaValue(): void
    {
        $website = new Website();

        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->assertSame($website, $this->provider->getCriteriaValue());
    }

    public function testGetCriteriaValueType(): void
    {
        $this->assertEquals(Website::class, $this->provider->getCriteriaValueType());
    }
}
