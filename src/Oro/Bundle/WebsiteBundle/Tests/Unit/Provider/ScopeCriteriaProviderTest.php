<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Provider;

use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;

class ScopeCriteriaProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ScopeCriteriaProvider
     */
    private $provider;

    /**
     * @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $websiteManager;

    protected function setUp()
    {
        $this->websiteManager = $this->getMockBuilder(WebsiteManager::class)->disableOriginalConstructor()->getMock();
        $this->provider = new ScopeCriteriaProvider($this->websiteManager);
    }

    public function testGetCriteriaForCurrentScope()
    {
        $website = new Website();
        $this->websiteManager
            ->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);
        $actual = $this->provider->getCriteriaForCurrentScope();
        $this->assertEquals(['website' => $website], $actual);
    }

    /**
     * @dataProvider contextDataProvider
     *
     * @param mixed $context
     * @param array $criteria
     */
    public function testGetCriteria($context, array $criteria)
    {
        $actual = $this->provider->getCriteriaByContext($context);
        $this->assertEquals($criteria, $actual);
    }

    /**
     * @return array
     */
    public function contextDataProvider()
    {
        $website = new Website();
        $websiteAware = new \stdClass();
        $websiteAware->website = $website;

        return [
            'array_context_with_website_key' => [
                'context' => ['website' => $website],
                'criteria' => ['website' => $website],
            ],
            'array_context_without_website_key' => [
                'context' => [],
                'criteria' => [],
            ],
            'object_context_website_aware' => [
                'context' => $websiteAware,
                'criteria' => ['website' => $website],
            ],
            'object_context_not_website_aware' => [
                'context' => new \stdClass(),
                'criteria' => [],
            ],
        ];
    }

    public function testGetCriteriaValueType()
    {
        $this->assertEquals(Website::class, $this->provider->getCriteriaValueType());
    }
}
