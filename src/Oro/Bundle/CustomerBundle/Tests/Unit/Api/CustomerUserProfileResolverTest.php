<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Api;

use Oro\Bundle\ApiBundle\Processor\Context;
use Oro\Bundle\CustomerBundle\Api\CustomerUserProfileResolver;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CustomerUserProfileResolverTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    /** @var CustomerUserProfileResolver */
    private $resolver;

    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->resolver = new CustomerUserProfileResolver($this->tokenAccessor, $this->authorizationChecker);

        parent::setUp();
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

    /**
     * @param string $className
     *
     * @return Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getContext(string $className = CustomerUser::class)
    {
        $context = $this->createMock(Context::class);
        $context->expects($this->once())
            ->method('getClassName')
            ->willReturn($className);

        return $context;
    }
}
