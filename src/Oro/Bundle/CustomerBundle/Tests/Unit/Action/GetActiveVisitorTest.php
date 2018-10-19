<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Action;

use Oro\Bundle\CustomerBundle\Action\GetActiveVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Component\ConfigExpression\Tests\Unit\Fixtures\ItemStub;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GetActiveVisitorTest extends \PHPUnit\Framework\TestCase
{
    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    /** @var GetActiveVisitor */
    private $action;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->action = new GetActiveVisitor(new ContextAccessor(), $this->tokenStorage);
    }

    public function testExecute()
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->createMock(EventDispatcher::class);
        $this->action->setDispatcher($dispatcher);

        $customerVisitor = new CustomerVisitor();

        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $token->expects($this->once())
            ->method('getVisitor')
            ->willReturn($customerVisitor);
        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $context = new ItemStub();

        $this->action->initialize(['attribute' => new PropertyPath('some_attribute')]);
        $this->action->execute($context);

        $attributeName = 'some_attribute';
        $this->assertEquals($customerVisitor, $context->$attributeName);
    }

    /**
     * @expectedException \Oro\Component\Action\Exception\ActionException
     * @expectedExceptionMessage Can't extract active visitor
     */
    public function testExecuteInvalidToken()
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->createMock(EventDispatcher::class);
        $this->action->setDispatcher($dispatcher);

        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $token->expects($this->once())
            ->method('getVisitor')
            ->willReturn(null);
        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->action->initialize(['attribute' => new PropertyPath('some_attribute')]);
        $this->action->execute(new ItemStub());
    }

    /**
     * @expectedException \Oro\Component\Action\Exception\ActionException
     * @expectedExceptionMessage Can't extract active visitor
     */
    public function testExecuteEmptyVisitor()
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->createMock(EventDispatcher::class);
        $this->action->setDispatcher($dispatcher);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->action->initialize(['attribute' => new PropertyPath('some_attribute')]);
        $this->action->execute(new ItemStub());
    }
}
