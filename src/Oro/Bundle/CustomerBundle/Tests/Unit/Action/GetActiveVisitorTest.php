<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Action;

use Oro\Bundle\CustomerBundle\Action\GetActiveVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Component\Action\Exception\ActionException;
use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Component\ConfigExpression\Tests\Unit\Fixtures\ItemStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GetActiveVisitorTest extends TestCase
{
    private TokenStorageInterface&MockObject $tokenStorage;
    private GetActiveVisitor $action;

    #[\Override]
    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->action = new GetActiveVisitor(new ContextAccessor(), $this->tokenStorage);
        $this->action->setDispatcher($this->createMock(EventDispatcher::class));
    }

    public function testExecute(): void
    {
        $customerVisitor = new CustomerVisitor();

        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $token->expects($this->once())
            ->method('getVisitor')
            ->willReturn($customerVisitor);
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $context = new ItemStub();

        $this->action->initialize(['attribute' => new PropertyPath('some_attribute')]);
        $this->action->execute($context);

        $attributeName = 'some_attribute';
        $this->assertEquals($customerVisitor, $context->{$attributeName});
    }

    public function testExecuteInvalidToken(): void
    {
        $this->expectException(ActionException::class);
        $this->expectExceptionMessage("Can't extract active visitor");

        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $token->expects($this->once())
            ->method('getVisitor')
            ->willReturn(null);
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->action->initialize(['attribute' => new PropertyPath('some_attribute')]);
        $this->action->execute(new ItemStub());
    }

    public function testExecuteEmptyVisitor(): void
    {
        $this->expectException(ActionException::class);
        $this->expectExceptionMessage("Can't extract active visitor");

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->action->initialize(['attribute' => new PropertyPath('some_attribute')]);
        $this->action->execute(new ItemStub());
    }
}
