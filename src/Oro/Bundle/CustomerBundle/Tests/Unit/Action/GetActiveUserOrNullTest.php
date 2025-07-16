<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Action;

use Oro\Bundle\CustomerBundle\Action\GetActiveUserOrNull;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Component\ConfigExpression\Tests\Unit\Fixtures\ItemStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GetActiveUserOrNullTest extends TestCase
{
    private const ATTRIBUTE_NAME = 'some_attribute';

    private TokenStorageInterface&MockObject $tokenStorage;
    private GetActiveUserOrNull $action;

    #[\Override]
    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->action = new GetActiveUserOrNull(new ContextAccessor(), $this->tokenStorage);
        $this->action->setDispatcher($this->createMock(EventDispatcher::class));
    }

    public function testExecute(): void
    {
        $customerUser = new CustomerUser();
        $customerVisitor = new CustomerVisitor();
        $customerVisitor->setCustomerUser($customerUser);

        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $token->expects($this->once())
            ->method('getVisitor')
            ->willReturn($customerVisitor);
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $context = new ItemStub();

        $this->action->initialize(['attribute' => new PropertyPath(self::ATTRIBUTE_NAME)]);
        $this->action->execute($context);

        $attributeName = self::ATTRIBUTE_NAME;
        $this->assertEquals($customerUser, $context->{$attributeName});
    }

    public function testExecuteNull(): void
    {
        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $token->expects($this->once())
            ->method('getVisitor')
            ->willReturn(new CustomerVisitor());
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $context = new ItemStub();

        $this->action->initialize(['attribute' => new PropertyPath(self::ATTRIBUTE_NAME)]);
        $this->action->execute($context);

        $attributeName = self::ATTRIBUTE_NAME;
        $this->assertNull($context->{$attributeName});
    }
}
