<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Action;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Oro\Bundle\CustomerBundle\Action\GetOrCreateActiveUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\GuestCustomerUserManager;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Component\ConfigExpression\Tests\Unit\Fixtures\ItemStub;

class GetOrCreateActiveUserTest extends \PHPUnit_Framework_TestCase
{
    const ATTRIBUTE_NAME = 'some_attribute';

    /**
     * @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenStorage;

    /**
     * @var GuestCustomerUserManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $guestCustomerUserManager;

    /**
     * @var GetOrCreateActiveUser
     */
    protected $action;

    protected function setUp()
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->guestCustomerUserManager = $this->createMock(GuestCustomerUserManager::class);

        $this->action = new GetOrCreateActiveUser(
            new ContextAccessor(),
            $this->tokenStorage,
            $this->guestCustomerUserManager
        );
    }

    public function testExecuteWithCustomerUser()
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->createMock(EventDispatcher::class);
        $this->action->setDispatcher($dispatcher);

        $token = $this->createMock(TokenInterface::class);
        $customerUser = new CustomerUser();
        $token->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($customerUser));

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $context = new ItemStub();

        $this->action->initialize(['attribute' => new PropertyPath(self::ATTRIBUTE_NAME)]);
        $this->action->execute($context);

        $attributeName = self::ATTRIBUTE_NAME;
        $this->assertEquals($customerUser, $context->$attributeName);
    }

    public function testExecuteWithCustomerVisitor()
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

        $customerUser = new CustomerUser();
        $this->guestCustomerUserManager->expects($this->once())
            ->method('getOrCreate')
            ->will($this->returnValue($customerUser));

        $context = new ItemStub();

        $this->action->initialize(['attribute' => new PropertyPath(self::ATTRIBUTE_NAME)]);
        $this->action->execute($context);

        $attributeName = self::ATTRIBUTE_NAME;
        $this->assertEquals($customerUser, $context->$attributeName);
    }
}
