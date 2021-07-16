<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserScopeCacheKeyBuilder;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\ScopeBundle\Manager\ScopeCacheKeyBuilderInterface;
use Oro\Bundle\ScopeBundle\Model\ScopeCriteria;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CustomerUserScopeCacheKeyBuilderTest extends \PHPUnit\Framework\TestCase
{
    private function getInnerBuilder(ScopeCriteria $criteria, ?string $cacheKey): ScopeCacheKeyBuilderInterface
    {
        $innerBuilder = $this->createMock(ScopeCacheKeyBuilderInterface::class);
        $innerBuilder->expects($this->any())
            ->method('getCacheKey')
            ->with($this->identicalTo($criteria))
            ->willReturn($cacheKey);

        return $innerBuilder;
    }

    public function testGetCacheKeyForNotFrontendRequest()
    {
        $criteria = $this->createMock(ScopeCriteria::class);

        $frontendHelper = $this->createMock(FrontendHelper::class);
        $frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $builder = new CustomerUserScopeCacheKeyBuilder(
            $this->getInnerBuilder($criteria, 'data'),
            $this->createMock(TokenStorageInterface::class),
            $this->createMock(WebsiteManager::class),
            $frontendHelper
        );
        $this->assertEquals('data', $builder->getCacheKey($criteria));
    }

    public function testGetCacheKeyWhenNoToken()
    {
        $criteria = $this->createMock(ScopeCriteria::class);

        $frontendHelper = $this->createMock(FrontendHelper::class);
        $frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $builder = new CustomerUserScopeCacheKeyBuilder(
            $this->getInnerBuilder($criteria, 'data'),
            $tokenStorage,
            $this->createMock(WebsiteManager::class),
            $frontendHelper
        );
        $this->assertNull($builder->getCacheKey($criteria));
    }

    public function testGetCacheKeyForUnsupportedUserType()
    {
        $criteria = $this->createMock(ScopeCriteria::class);

        $frontendHelper = $this->createMock(FrontendHelper::class);
        $frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn('test');

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $builder = new CustomerUserScopeCacheKeyBuilder(
            $this->getInnerBuilder($criteria, 'data'),
            $tokenStorage,
            $this->createMock(WebsiteManager::class),
            $frontendHelper
        );
        $this->assertNull($builder->getCacheKey($criteria));
    }

    public function testGetCacheKeyForVisitor()
    {
        $criteria = $this->createMock(ScopeCriteria::class);

        $frontendHelper = $this->createMock(FrontendHelper::class);
        $frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $token->expects($this->never())
            ->method('getUser');

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $website = $this->createMock(Website::class);
        $website->expects($this->once())
            ->method('getId')
            ->willReturn(100);

        $websiteManager = $this->createMock(WebsiteManager::class);
        $websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $builder = new CustomerUserScopeCacheKeyBuilder(
            $this->getInnerBuilder($criteria, 'data'),
            $tokenStorage,
            $websiteManager,
            $frontendHelper
        );
        $this->assertEquals('data;customerUser=anonymous;website=100', $builder->getCacheKey($criteria));
    }

    public function testGetCacheKeyForCustomerUser()
    {
        $criteria = $this->createMock(ScopeCriteria::class);

        $frontendHelper = $this->createMock(FrontendHelper::class);
        $frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $token = $this->createMock(OrganizationAwareTokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($customerUser);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $website = $this->createMock(Website::class);
        $website->expects($this->once())
            ->method('getId')
            ->willReturn(100);

        $websiteManager = $this->createMock(WebsiteManager::class);
        $websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $builder = new CustomerUserScopeCacheKeyBuilder(
            $this->getInnerBuilder($criteria, 'data'),
            $tokenStorage,
            $websiteManager,
            $frontendHelper
        );
        $this->assertEquals('data;customerUser=1;website=100', $builder->getCacheKey($criteria));
    }

    public function testGetCacheKeyForCustomerUserWithoutCurrentWebsite()
    {
        $criteria = $this->createMock(ScopeCriteria::class);

        $frontendHelper = $this->createMock(FrontendHelper::class);
        $frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $organization = $this->createMock(Organization::class);
        $organization->expects($this->once())
            ->method('getId')
            ->willReturn(200);

        $token = $this->createMock(OrganizationAwareTokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($customerUser);
        $token->expects($this->once())
            ->method('getOrganization')
            ->willReturn($organization);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $websiteManager = $this->createMock(WebsiteManager::class);
        $websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn(null);

        $builder = new CustomerUserScopeCacheKeyBuilder(
            $this->getInnerBuilder($criteria, 'data'),
            $tokenStorage,
            $websiteManager,
            $frontendHelper
        );
        $this->assertEquals('data;customerUser=1;organization=200', $builder->getCacheKey($criteria));
    }
}
