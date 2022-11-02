<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Exception\EmptyCustomerException;
use Oro\Bundle\CustomerBundle\Exception\GuestCustomerUserLoginException;
use Oro\Bundle\CustomerBundle\Security\UserChecker;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Exception\EmptyOwnerException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCheckerTest extends \PHPUnit\Framework\TestCase
{
    /** @var UserCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $innerUserChecker;

    /** @var UserChecker */
    private $userChecker;

    protected function setUp(): void
    {
        $this->innerUserChecker = $this->createMock(UserCheckerInterface::class);

        $this->userChecker = new UserChecker($this->innerUserChecker);
    }

    public function testCheckPostAuthForNotCustomerUser()
    {
        $user = $this->createMock(UserInterface::class);

        $this->innerUserChecker->expects(self::once())
            ->method('checkPostAuth')
            ->with(self::identicalTo($user));

        $this->userChecker->checkPostAuth($user);
    }

    public function testCheckPostAuthForCustomerUser()
    {
        $user = new CustomerUser();
        $user->setCustomer(new Customer());
        $user->setOwner(new User());

        $this->innerUserChecker->expects(self::once())
            ->method('checkPostAuth')
            ->with(self::identicalTo($user));

        $this->userChecker->checkPostAuth($user);
    }

    public function testCheckPostAuthForDisabledCustomerUser()
    {
        $this->expectException(DisabledException::class);

        $user = new CustomerUser();
        $user->setEnabled(false);

        $this->innerUserChecker->expects(self::once())
            ->method('checkPostAuth')
            ->with(self::identicalTo($user));

        $this->userChecker->checkPostAuth($user);
    }

    public function testCheckPostAuthForNotConfirmedCustomerUser()
    {
        $this->expectException(LockedException::class);

        $user = new CustomerUser();
        $user->setConfirmed(false);

        $this->innerUserChecker->expects(self::once())
            ->method('checkPostAuth')
            ->with(self::identicalTo($user));

        $this->userChecker->checkPostAuth($user);
    }

    public function testCheckPostAuthForCustomerUserWithoutCustomer()
    {
        $this->expectException(EmptyCustomerException::class);

        $user = new CustomerUser();
        $user->setOwner(new User());

        $this->innerUserChecker->expects(self::once())
            ->method('checkPostAuth')
            ->with(self::identicalTo($user));

        $this->userChecker->checkPostAuth($user);
    }

    public function testCheckPostAuthForCustomerUserWithoutOwner()
    {
        $this->expectException(EmptyOwnerException::class);

        $user = new CustomerUser();
        $user->setCustomer(new Customer());

        $this->innerUserChecker->expects(self::once())
            ->method('checkPostAuth')
            ->with(self::identicalTo($user));

        $this->userChecker->checkPostAuth($user);
    }

    public function testCheckPreAuthForNotCustomerUser()
    {
        $user = $this->createMock(UserInterface::class);

        $this->innerUserChecker->expects(self::once())
            ->method('checkPreAuth')
            ->with(self::identicalTo($user));

        $this->userChecker->checkPreAuth($user);
    }

    public function testCheckPreAuthForCustomerUser()
    {
        $user = new CustomerUser();

        $this->innerUserChecker->expects(self::once())
            ->method('checkPreAuth')
            ->with(self::identicalTo($user));

        $this->userChecker->checkPreAuth($user);
    }

    public function testCheckPreAuthForGuestCustomerUser()
    {
        $this->expectException(GuestCustomerUserLoginException::class);

        $user = new CustomerUser();
        $user->setIsGuest(true);

        $this->innerUserChecker->expects(self::never())
            ->method('checkPreAuth');

        $this->userChecker->checkPreAuth($user);
    }
}
