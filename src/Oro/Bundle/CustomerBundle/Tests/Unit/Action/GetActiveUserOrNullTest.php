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
    const ATTRIBUTE_NAME = 'some_attribute';

    /**
     * @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $tokenStorage;

    /**
     * @var GetActiveUserOrNull
     */
    protected $action;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->action = new GetActiveUserOrNull(new ContextAccessor(), $this->tokenStorage);
    }

    public function testExecute()
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->createMock(EventDispatcher::class);
        $this->action->setDispatcher($dispatcher);

        $customerUser = new CustomerUser();
        $customerVisitor = new CustomerVisitor();
        $customerVisitor->setCustomerUser($customerUser);

        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $token->expects($this->once())
            ->method('getVisitor')
            ->will($this->returnValue($customerVisitor));
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $context = new ItemStub();

        $this->action->initialize(['attribute' => new PropertyPath(self::ATTRIBUTE_NAME)]);
        $this->action->execute($context);

        $attributeName = self::ATTRIBUTE_NAME;
        $this->assertEquals($customerUser, $context->$attributeName);
    }

    public function testExecuteNull()
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->createMock(EventDispatcher::class);
        $this->action->setDispatcher($dispatcher);

        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $token->expects($this->once())
            ->method('getVisitor')
            ->will($this->returnValue(new CustomerVisitor()));
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $context = new ItemStub();

        $this->action->initialize(['attribute' => new PropertyPath(self::ATTRIBUTE_NAME)]);
        $this->action->execute($context);

        $attributeName = self::ATTRIBUTE_NAME;
        $this->assertNull($context->$attributeName);
    }
}
