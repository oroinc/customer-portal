<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Cache;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Cache\DoctrineAclCacheCustomerUserInfoProvider;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Cache\CacheInstantiatorInterface;
use Oro\Bundle\SecurityBundle\Cache\DoctrineAclCacheProvider;
use Oro\Bundle\SecurityBundle\Cache\DoctrineAclCacheUserInfoProvider;
use Oro\Component\Testing\Unit\EntityTrait;

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

        $namespacesCache = $this->createMock(CacheProvider::class);
        $cache = $this->createMock(CacheProvider::class);

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
            ->method('fetch')
            ->with('Customer_2')
            ->willReturn([1548 => true, 1549 => true]);

        self::assertSame($cache, $this->aclCacheProvider->getCurrentUserCache());
    }

    public function testGetCurrentUserCacheWithNotKnownCache(): void
    {
        $customer = $this->getEntity(Customer::class, ['id' => 1548]);
        $user = new CustomerUser();
        $user->setCustomer($customer);

        $namespacesCache = $this->createMock(CacheProvider::class);
        $cache = $this->createMock(CacheProvider::class);

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
            ->method('fetch')
            ->willReturnMap([
                ['Customer_2', []],
                ['itemsList', []]
            ]);

        $namespacesCache->expects(self::exactly(2))
            ->method('save')
            ->withConsecutive(
                ['Customer_2', [1548 => true]],
                ['itemsList', [Customer::class =>[2]]]
            )->willReturn(true);

        self::assertSame($cache, $this->aclCacheProvider->getCurrentUserCache());
    }
}
