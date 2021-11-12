<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Action;

use Oro\Bundle\CustomerBundle\Action\GetActiveUserOrNull;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Component\ConfigExpression\Tests\Unit\Fixtures\ItemStub;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GetActiveUserOrNullTest extends \PHPUnit\Framework\TestCase
{
    private const ATTRIBUTE_NAME = 'some_attribute';

    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    /** @var GetActiveUserOrNull */
    private $action;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->action = new GetActiveUserOrNull(new ContextAccessor(), $this->tokenStorage);
        $this->action->setDispatcher($this->createMock(EventDispatcher::class));
    }

    public function testExecute()
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

    public function testExecuteNull()
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
