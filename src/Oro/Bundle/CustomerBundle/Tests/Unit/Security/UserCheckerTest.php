<?php

namespace Oro\Bundle\CustomerBundle\Tests\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\UserChecker;
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

    protected function setUp()
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
        $this->innerUserChecker->expects($this->once())
            ->method('checkPreAuth')
            ->with($user);

        $this->userChecker->checkPreAuth($user);
    }

    /**
     * @expectedException \Oro\Bundle\CustomerBundle\Exception\GuestCustomerUserLoginException
     * @expectedExceptionMessage Customer User is Guest.
     */
    public function testPreAuthWithGuestCustomerUser()
    {
        $user = new CustomerUser();
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
}
