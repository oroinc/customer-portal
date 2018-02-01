<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Provider;

use Doctrine\Common\Cache\ArrayCache;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Provider\CacheableWebsiteProvider;
use Oro\Bundle\WebsiteBundle\Provider\WebsiteProvider;
use Oro\Component\Testing\Unit\EntityTrait;

class CacheableWebsiteProviderTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /** @var WebsiteProvider|\PHPUnit_Framework_MockObject_MockObject */
    private $websiteProvider;

    /** @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject */
    private $doctrineHelper;

    /** @var CacheableWebsiteProvider */
    private $cacheableProvider;

    protected function setUp()
    {
        $this->websiteProvider = $this->createMock(WebsiteProvider::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);

        $this->cacheableProvider = new CacheableWebsiteProvider(
            $this->websiteProvider,
            new ArrayCache(),
            $this->doctrineHelper
        );
    }

    public function testGetWebsites()
    {
        $websiteId = 123;
        $website = $this->getWebsite($websiteId);

        $this->websiteProvider->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn([$websiteId]);

        $this->doctrineHelper->expects($this->atLeastOnce())
            ->method('getEntityReference')
            ->with(Website::class, $websiteId)
            ->willReturn($website);

        $this->assertEquals([$website->getId() => $website], $this->cacheableProvider->getWebsites());
        // test the result is cached
        $this->assertEquals([$website->getId() => $website], $this->cacheableProvider->getWebsites());
    }

    public function testGetWebsiteIds()
    {
        $ids = [1001, 1002, 1003];

        $this->websiteProvider->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn($ids);

        $this->assertEquals($ids, $this->cacheableProvider->getWebsiteIds());
        // test the result is cached
        $this->assertEquals($ids, $this->cacheableProvider->getWebsiteIds());
    }

    public function testHasCacheAndClearCacheForGetWebsites()
    {
        $websiteId = 123;
        $website = $this->getWebsite($websiteId);

        $this->websiteProvider->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn([$websiteId]);

        $this->doctrineHelper->expects($this->atLeastOnce())
            ->method('getEntityReference')
            ->with(Website::class, $websiteId)
            ->willReturn($website);

        $this->assertFalse($this->cacheableProvider->hasCache());

        $this->cacheableProvider->getWebsites();
        $this->assertTrue($this->cacheableProvider->hasCache());

        $this->cacheableProvider->clearCache();
        $this->assertFalse($this->cacheableProvider->hasCache());
    }

    public function testHasCacheAndClearCacheForGetWebsiteIds()
    {
        $this->websiteProvider->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn([1001, 1002, 1003]);

        $this->assertFalse($this->cacheableProvider->hasCache());

        $this->cacheableProvider->getWebsiteIds();
        $this->assertTrue($this->cacheableProvider->hasCache());

        $this->cacheableProvider->clearCache();
        $this->assertFalse($this->cacheableProvider->hasCache());
    }

    /**
     * @param int $id
     * @return object|Website
     */
    protected function getWebsite($id)
    {
        return $this->getEntity(Website::class, ['id' => $id]);
    }
}
