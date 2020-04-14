<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Provider;

use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;

class ScopeCriteriaProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var ScopeCriteriaProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->provider = new ScopeCriteriaProvider($this->websiteManager);
    }

    public function testGetCriteriaField()
    {
        $this->assertEquals(ScopeCriteriaProvider::WEBSITE, $this->provider->getCriteriaField());
    }

    public function testGetCriteriaValue()
    {
        $website = new Website();

        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->assertSame($website, $this->provider->getCriteriaValue());
    }

    public function testGetCriteriaValueType()
    {
        $this->assertEquals(Website::class, $this->provider->getCriteriaValueType());
    }
}
