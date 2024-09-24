<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\Cache;

use Oro\Bundle\CustomerBundle\Acl\Cache\CustomerVisitorAclCache;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CustomerVisitorAclCacheTest extends \PHPUnit\Framework\TestCase
{
    /** @var CacheItemPoolInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $cache;

    /** @var CacheItemInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $cacheItem;

    /** @var CustomerVisitorAclCache */
    private $customerVisitorAclCache;

    #[\Override]
    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheItemPoolInterface::class);
        $this->cacheItem = $this->createMock(CacheItemInterface::class);
        $this->customerVisitorAclCache = new CustomerVisitorAclCache($this->cache);
    }

    public function testCacheAclResult(): void
    {
        $this->cache->expects(self::once())
            ->method('getItem')
            ->with('website_10')
            ->willReturn($this->cacheItem);

        $this->cacheItem->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $this->cacheItem->expects(self::once())
            ->method('get')
            ->willReturn(['someData@VIEW' => 1]);

        $this->cacheItem->expects(self::once())
            ->method('set')
            ->with(['someData@VIEW' => 1, 'newData@EDIT' => -1]);

        $this->cache->expects(self::once())
            ->method('save')
            ->with($this->cacheItem);

        $this->customerVisitorAclCache->cacheAclResult(10, 'newData', ['EDIT'], -1);
    }

    public function testIsVoteResultExistOnExistingData(): void
    {
        $this->cache->expects(self::once())
            ->method('getItem')
            ->with('website_11')
            ->willReturn($this->cacheItem);

        $this->cacheItem->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $this->cacheItem->expects(self::once())
            ->method('get')
            ->willReturn(['someData@VIEW' => 1]);

        self::assertTrue($this->customerVisitorAclCache->isVoteResultExist(11, 'someData', ['VIEW']));
        // check that data was cached in object cache and do not reload from the real cache
        self::assertFalse($this->customerVisitorAclCache->isVoteResultExist(11, 'notExistingData', ['VIEW']));
    }

    public function testIsVoteResultExistOnNonExistingData(): void
    {
        $this->cache->expects(self::once())
            ->method('getItem')
            ->with('website_12')
            ->willReturn($this->cacheItem);

        $this->cacheItem->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $this->cacheItem->expects(self::once())
            ->method('get')
            ->willReturn(['someData@VIEW' => 1]);

        self::assertFalse($this->customerVisitorAclCache->isVoteResultExist(12, 'notExistingData', ['VIEW']));
    }

    public function testGetVoteResultOnExistingData(): void
    {
        $this->cache->expects(self::once())
            ->method('getItem')
            ->with('website_11')
            ->willReturn($this->cacheItem);

        $this->cacheItem->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $this->cacheItem->expects(self::once())
            ->method('get')
            ->willReturn(['someData@VIEW' => 1, 'anotherData@VIEW' => -1]);

        self::assertEquals(1, $this->customerVisitorAclCache->getVoteResult(11, 'someData', ['VIEW']));
        // check that data was cached in object cache and do not reload from the real cache
        self::assertEquals(-1, $this->customerVisitorAclCache->getVoteResult(11, 'anotherData', ['VIEW']));
    }

    public function testGetVoteResultOnNonExistingData(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Customer visitor ACL cache have no data for given arguments.'
            . ' Please use isVoteResultExist method to check if data exist.');

        $this->cache->expects(self::once())
            ->method('getItem')
            ->with('website_12')
            ->willReturn($this->cacheItem);

        $this->cacheItem->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $this->cacheItem->expects(self::once())
            ->method('get')
            ->willReturn(['someData@VIEW' => 1]);

        $this->customerVisitorAclCache->getVoteResult(12, 'notExistingData', ['VIEW']);
    }

    public function testClearWebsiteData(): void
    {
        $this->cache->expects(self::once())
            ->method('hasItem')
            ->with('website_13')
            ->willReturn(true);

        $this->cache->expects(self::once())
            ->method('deleteItem')
            ->with('website_13')
            ->willReturn(true);

        $this->customerVisitorAclCache->clearWebsiteData(13);
    }

    public function testClear(): void
    {
        $this->cache->expects(self::once())
            ->method('clear')
            ->willReturn(true);

        $this->customerVisitorAclCache->clearCache();
    }
}
