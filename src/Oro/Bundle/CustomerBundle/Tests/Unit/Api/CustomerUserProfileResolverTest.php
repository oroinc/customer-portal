<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Api;

use Oro\Bundle\ApiBundle\Processor\Context;
use Oro\Bundle\CustomerBundle\Api\CustomerUserProfileResolver;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CustomerUserProfileResolverTest extends TestCase
{
    use EntityTrait;

    private TokenAccessorInterface&MockObject $tokenAccessor;
    private AuthorizationCheckerInterface&MockObject $authorizationChecker;
    private CustomerUserProfileResolver $resolver;

    #[\Override]
    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->resolver = new CustomerUserProfileResolver($this->tokenAccessor, $this->authorizationChecker);
    }

    public function testProfileWithoutCustomerUser(): void
    {
        $customerUser = $this->getEntity(CustomerUser::class, ['id' => 1]);
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $this->assertFalse($this->resolver->hasProfilePermission($this->getContext(), $customerUser->getId()));
    }

    public function testProfileWithProfilePermission(): void
    {
        $customerUser = $this->getEntity(CustomerUser::class, ['id' => 1]);
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($customerUser);

        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->willReturnMap([
                ['EDIT', 'entity:' . CustomerUser::class, false],
                ['oro_customer_frontend_update_own_profile', null, true],
            ]);

        $this->assertTrue($this->resolver->hasProfilePermission($this->getContext(), $customerUser->getId()));
    }

    public function testProfileWithCustomerUserUpdatePermission(): void
    {
        $customerUser = $this->getEntity(CustomerUser::class, ['id' => 1]);
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($customerUser);

        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->willReturnMap([
                ['EDIT', 'entity:' . CustomerUser::class, true],
                ['oro_customer_frontend_update_own_profile', null, true],
            ]);

        $this->assertFalse($this->resolver->hasProfilePermission($this->getContext(), $customerUser->getId()));
    }

    private function getContext(string $className = CustomerUser::class): Context&MockObject
    {
        $context = $this->createMock(Context::class);
        $context->expects($this->once())
            ->method('getClassName')
            ->willReturn($className);

        return $context;
    }
}
