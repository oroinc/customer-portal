<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\UserChecker;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

class UserCheckerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UserCheckerInterface
     */
    private $userChecker;

    /**
     * @var UserCheckerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $innerUserChecker;

    protected function setUp(): void
    {
        $this->innerUserChecker = $this->createMock(UserCheckerInterface::class);
        $this->userChecker = new UserChecker($this->innerUserChecker);
    }


    public function testCheckPostAuth()
    {
        $user = $this->createMock(UserInterface::class);
        $this->innerUserChecker->expects($this->once())
            ->method('checkPostAuth')
            ->with($user);

        $this->userChecker->checkPostAuth($user);
    }

    public function testPreAuthWithCustomerUser()
    {
        $user = new CustomerUser();
        $user->setOwner(new User());
        $user->setCustomer(new Customer());
        $this->innerUserChecker->expects($this->once())
            ->method('checkPreAuth')
            ->with($user);

        $this->userChecker->checkPreAuth($user);
    }

    public function testPreAuthWithGuestCustomerUser()
    {
        $this->expectException(\Oro\Bundle\CustomerBundle\Exception\GuestCustomerUserLoginException::class);
        $user = new CustomerUser();
        $user->setCustomer(new Customer());
        $user->setIsGuest(true);
        $this->innerUserChecker->expects($this->never())
            ->method('checkPreAuth');

        $this->userChecker->checkPreAuth($user);
    }

    public function testPreAuthWithDifferentUser()
    {
        $user = $this->createMock(UserInterface::class);
        $this->innerUserChecker->expects($this->once())
            ->method('checkPreAuth')
            ->with($user);

        $this->userChecker->checkPreAuth($user);
    }

    public function testCheckPostAuthWithCustomerUserWithoutCustomer()
    {
        $this->expectException(\Oro\Bundle\CustomerBundle\Exception\EmptyCustomerException::class);
        $user = new CustomerUser();
        $user->setOwner(new User());

        $this->userChecker->checkPostAuth($user);
    }

    public function testCheckPostAuthWithCustomerUserWithoutOwner()
    {
        $this->expectException(\Oro\Bundle\UserBundle\Exception\EmptyOwnerException::class);
        $user = new CustomerUser();
        $user->setCustomer(new Customer());

        $this->userChecker->checkPostAuth($user);
    }
}
