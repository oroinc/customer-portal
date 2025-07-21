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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;

class DoctrineAclCacheProviderTest extends TestCase
{
    use EntityTrait;

    private TokenAccessorInterface&MockObject $tokenAccessor;
    private CacheInstantiatorInterface&MockObject $cacheInstantiator;
    protected DoctrineAclCacheProvider $aclCacheProvider;

    #[\Override]
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
            ->willReturn($batchItem = new CacheItem());

        $isHitReflection = new \ReflectionProperty($batchItem, 'isHit');
        $isHitReflection->setValue($batchItem, true);
        $batchItem->set([1548 => true, 1549 => true]);

        self::assertSame($cache, $this->aclCacheProvider->getCurrentUserCache());
    }

    public function testGetCurrentUserCacheWithNotKnownCache(): void
    {
        $customer = $this->getEntity(Customer::class, ['id' => 1548]);
        $user = new CustomerUser();
        $user->setCustomer($customer);

        $namespacesCache = $this->createMock(AdapterInterface::class);
        $cache = $this->createMock(AdapterInterface::class);
        $batchItem = new CacheItem();
        $batchListItem = new CacheItem();

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

        $batchItem->set([1254 => true]);
        $batchListItem->set([[User::class => [2]]]);

        self::assertSame($cache, $this->aclCacheProvider->getCurrentUserCache());
    }
}
