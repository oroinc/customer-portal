<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Cache;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Cache\DoctrineAclCacheCustomerUserInfoProvider;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Cache\CacheInstantiatorInterface;
use Oro\Bundle\SecurityBundle\Cache\DoctrineAclCacheProvider;
use Oro\Bundle\SecurityBundle\Cache\DoctrineAclCacheUserInfoProvider;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;

class DoctrineAclCacheProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var CacheInstantiatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $cacheInstantiator;

    protected DoctrineAclCacheProvider $aclCacheProvider;

    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->cacheInstantiator = $this->createMock(CacheInstantiatorInterface::class);

        $this->aclCacheProvider = new DoctrineAclCacheProvider(
            $this->cacheInstantiator,
            new DoctrineAclCacheCustomerUserInfoProvider(
                $this->tokenAccessor,
                new DoctrineAclCacheUserInfoProvider($this->tokenAccessor)
            )
        );
    }

    public function testGetCurrentUserCacheWithKnownCache(): void
    {
        $customer = $this->getEntity(Customer::class, ['id' => 1548]);
        $user = new CustomerUser();
        $user->setCustomer($customer);

        $namespacesCache = $this->createMock(AdapterInterface::class);
        $cache = $this->createMock(AdapterInterface::class);
        $batchItem = $this->createMock(ItemInterface::class);

        $this->tokenAccessor->expects(self::once())
            ->method('hasUser')
            ->willReturn(true);

        $this->tokenAccessor->expects(self::exactly(2))
            ->method('getUser')
            ->willReturn($user);

        $this->cacheInstantiator->expects(self::exactly(2))
            ->method('getCacheInstance')
            ->willReturnMap([
                ['doctrine_acl_Customer_1548', $cache],
                ['doctrine_acl_namespaces', $namespacesCache]
            ]);

        $namespacesCache->expects(self::once())
            ->method('getItem')
            ->with('Customer_2')
            ->willReturn($batchItem);

        $batchItem->expects(self::once())
            ->method('isHit')
            ->willReturn(true);
        $batchItem->expects(self::once())
            ->method('get')
            ->willReturn([1548 => true, 1549 => true]);

        self::assertSame($cache, $this->aclCacheProvider->getCurrentUserCache());
    }

    public function testGetCurrentUserCacheWithNotKnownCache(): void
    {
        $customer = $this->getEntity(Customer::class, ['id' => 1548]);
        $user = new CustomerUser();
        $user->setCustomer($customer);

        $namespacesCache = $this->createMock(AdapterInterface::class);
        $cache = $this->createMock(AdapterInterface::class);
        $batchItem = $this->createMock(ItemInterface::class);
        $batchListItem = $this->createMock(ItemInterface::class);

        $this->tokenAccessor->expects(self::once())
            ->method('hasUser')
            ->willReturn(true);

        $this->tokenAccessor->expects(self::exactly(2))
            ->method('getUser')
            ->willReturn($user);

        $this->cacheInstantiator->expects(self::exactly(2))
            ->method('getCacheInstance')
            ->willReturnMap([
                ['doctrine_acl_Customer_1548', $cache],
                ['doctrine_acl_namespaces', $namespacesCache]
            ]);

        $namespacesCache->expects(self::exactly(2))
            ->method('getItem')
            ->willReturnMap([
                ['Customer_2', $batchItem],
                ['itemsList', $batchListItem]
            ]);

        $namespacesCache->expects(self::exactly(2))
            ->method('save')
            ->withConsecutive(
                [$batchItem],
                [$batchListItem]
            )->willReturn(true);

        $batchItem->expects(self::once())
            ->method('isHit')
            ->willReturn(false);
        $batchItem->expects(self::once())
            ->method('set')
            ->willReturn([1254 => true]);

        $batchListItem->expects(self::once())
            ->method('isHit')
            ->willReturn(false);
        $batchListItem->expects(self::once())
            ->method('set')
            ->willReturn([User::class => [2]]);

        self::assertSame($cache, $this->aclCacheProvider->getCurrentUserCache());
    }
}
