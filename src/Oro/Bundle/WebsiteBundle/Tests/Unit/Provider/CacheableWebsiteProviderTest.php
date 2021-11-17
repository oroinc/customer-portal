<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Provider;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Provider\CacheableWebsiteProvider;
use Oro\Bundle\WebsiteBundle\Provider\WebsiteProviderInterface;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\Cache\CacheTrait;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CacheableWebsiteProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait, CacheTrait;

    /** @var WebsiteProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteProvider;

    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    /** @var CacheableWebsiteProvider */
    private $cacheableProvider;

    protected function setUp(): void
    {
        $this->websiteProvider = $this->createMock(WebsiteProviderInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->cacheableProvider = new CacheableWebsiteProvider(
            $this->websiteProvider,
            $this->getArrayCache(),
            $this->tokenStorage
        );
    }

    private function getWebsite(int $id, string $name): Website
    {
        $website = new Website();
        ReflectionUtil::setId($website, $id);
        $website->setName($name);

        return $website;
    }

    public function testGetWebsites(): void
    {
        $websiteId = 123;
        $website = $this->getWebsite($websiteId, 'some');

        $this->tokenStorage->expects($this->exactly(2))
            ->method('getToken')
            ->willReturn(null);

        $this->websiteProvider->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$websiteId => $website]);

        $this->assertEquals([$website->getId() => $website], $this->cacheableProvider->getWebsites());
        // test the result is cached
        $this->assertEquals([$website->getId() => $website], $this->cacheableProvider->getWebsites());
    }

    public function testGetWebsiteChoices(): void
    {
        $websiteId = 123;
        $websiteName = 'test-website';
        $website = $this->getWebsite($websiteId, $websiteName);

        $this->tokenStorage->expects($this->exactly(2))
            ->method('getToken')
            ->willReturn(null);

        $this->websiteProvider->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$websiteId => $website]);

        $this->assertEquals([$website->getName() => $website->getId()], $this->cacheableProvider->getWebsiteChoices());
        // test the result is cached
        $this->assertEquals([$website->getName() => $website->getId()], $this->cacheableProvider->getWebsiteChoices());
    }

    public function testGetWebsiteIds(): void
    {
        $ids = [1001, 1002, 1003];

        $this->tokenStorage->expects($this->exactly(2))
            ->method('getToken')
            ->willReturn(null);

        $this->websiteProvider->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn($ids);

        $this->assertEquals($ids, $this->cacheableProvider->getWebsiteIds());
        // test the result is cached
        $this->assertEquals($ids, $this->cacheableProvider->getWebsiteIds());
    }

    public function testGetWebsiteIdsPerOrganization(): void
    {
        $organizationA = $this->createMock(OrganizationInterface::class);
        $tokenA = $this->createMock(OrganizationAwareTokenInterface::class);
        $tokenA->expects($this->atLeastOnce())
            ->method('getOrganization')
            ->willReturn($organizationA);
        $organizationA->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);

        $organizationB = $this->createMock(OrganizationInterface::class);
        $tokenB = $this->createMock(OrganizationAwareTokenInterface::class);
        $tokenB->expects($this->atLeastOnce())
            ->method('getOrganization')
            ->willReturn($organizationB);
        $organizationB->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(2);

        $this->tokenStorage->expects($this->exactly(4))
            ->method('getToken')
            ->willReturnOnConsecutiveCalls(
                $tokenA,
                $tokenB,
                $tokenA,
                $tokenB
            );

        $this->websiteProvider->expects($this->exactly(2))
            ->method('getWebsiteIds')
            ->willReturnOnConsecutiveCalls(
                [1, 2, 3],
                [41, 42, 43]
            );

        // Get websites for tokenA with organizationA
        $this->assertEquals([1, 2, 3], $this->cacheableProvider->getWebsiteIds());

        // Get websites for tokenB with organizationB
        $this->assertEquals([41, 42, 43], $this->cacheableProvider->getWebsiteIds());

        // Same data from cache
        $this->assertEquals([1, 2, 3], $this->cacheableProvider->getWebsiteIds());
        $this->assertEquals([41, 42, 43], $this->cacheableProvider->getWebsiteIds());
    }

    public function testClearCache(): void
    {
        $cacheProvider = $this->createMock(CacheProvider::class);

        $this->cacheableProvider = new CacheableWebsiteProvider(
            $this->websiteProvider,
            $cacheProvider,
            $this->tokenStorage
        );

        $cacheProvider
            ->expects($this->exactly(1))
            ->method('deleteAll');


        $this->cacheableProvider->clearCache();
    }
}
