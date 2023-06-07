<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\Cache;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\CustomerBundle\Acl\Cache\CustomerVisitorAclCache;

class CustomerVisitorAclCacheTest extends \PHPUnit\Framework\TestCase
{
    /** @var CacheProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $cache;

    /** @var CustomerVisitorAclCache */
    private $customerVisitorAclCache;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheProvider::class);
        $this->customerVisitorAclCache = new CustomerVisitorAclCache($this->cache);
    }

    public function testCacheAclResult(): void
    {
        $this->cache->expects(self::once())
            ->method('fetch')
            ->with('website_10')
            ->willReturn(['someData@VIEW' => 1]);

        $this->cache->expects(self::once())
            ->method('save')
            ->with('website_10', ['someData@VIEW' => 1, 'newData@EDIT' => -1]);

        $this->customerVisitorAclCache->cacheAclResult(10, 'newData', ['EDIT'], -1);
    }

    public function testIsVoteResultExistOnExistingData(): void
    {
        $this->cache->expects(self::once())
            ->method('fetch')
            ->with('website_11')
            ->willReturn(['someData@VIEW' => 1]);

        $this->cache->expects(self::never())
            ->method('save');

        self::assertTrue($this->customerVisitorAclCache->isVoteResultExist(11, 'someData', ['VIEW']));
        // check that data was cached in object cache and do not reload from the real cache
        self::assertFalse($this->customerVisitorAclCache->isVoteResultExist(11, 'notExistingData', ['VIEW']));
    }

    public function testIsVoteResultExistOnNonExistingData(): void
    {
        $this->cache->expects(self::once())
            ->method('fetch')
            ->with('website_12')
            ->willReturn(['someData@VIEW' => 1]);

        self::assertFalse($this->customerVisitorAclCache->isVoteResultExist(12, 'notExistingData', ['VIEW']));
    }

    public function testGetVoteResultOnExistingData(): void
    {
        $this->cache->expects(self::once())
            ->method('fetch')
            ->with('website_11')
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
            ->method('fetch')
            ->with('website_12')
            ->willReturn(['someData@VIEW' => 1]);

        $this->customerVisitorAclCache->getVoteResult(12, 'notExistingData', ['VIEW']);
    }

    public function testClearWebsiteData(): void
    {
        $this->cache->expects(self::once())
            ->method('contains')
            ->with('website_13')
            ->willReturn(true);

        $this->cache->expects(self::once())
            ->method('delete')
            ->with('website_13')
            ->willReturn(true);

        $this->customerVisitorAclCache->clearWebsiteData(13);
    }

    public function testClear(): void
    {
        $this->cache->expects(self::once())
            ->method('deleteAll')
            ->willReturn(true);

        $this->customerVisitorAclCache->clearCache();
    }
}
