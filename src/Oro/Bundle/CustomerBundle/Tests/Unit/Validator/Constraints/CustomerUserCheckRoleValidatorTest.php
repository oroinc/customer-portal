<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerUserCheckRole;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerUserCheckRoleValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CustomerUserCheckRoleValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CustomerUserCheckRoleValidator
     */
    protected $customerUserCheckRoleValidator;

    /**
     * @var CustomerUserCheckRole
     */
    protected $constraint;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ArrayCollection
     */
    protected $rolesCollection;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|CustomerUser
     */
    protected $customerUser;

    protected function setUp(): void
    {
        $this->customerUser = $this->createMock(CustomerUser::class);

        $this->rolesCollection = $this->getMockBuilder(ArrayCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->constraint = new CustomerUserCheckRole();

        $this->customerUserCheckRoleValidator = new CustomerUserCheckRoleValidator();
    }

    public function testCustomerUserWithRole()
    {
        $role = $this->createMock(CustomerUserRole::class);

        $this->customerUser->expects($this->once())
            ->method('getRoles')
            ->willReturn([$role]);

        $this->customerUser->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->customerUserCheckRoleValidator->validate($this->customerUser, $this->constraint);
    }

    public function testEnabledCustomerWithoutRole()
    {
        $this->customerUser->expects($this->once())
            ->method('getRoles')
            ->willReturn(null);

        $this->customerUser->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        /** @var ExecutionContextInterface|\PHPUnit\Framework\MockObject\MockObject $context */
        $context = $this->getMockBuilder(ExecutionContextInterface::class)->disableOriginalConstructor()->getMock();

        $context->expects($this->once())
            ->method('addViolation')
            ->with($this->constraint->message);

        $this->customerUserCheckRoleValidator->initialize($context);

        $this->customerUserCheckRoleValidator->validate($this->customerUser, $this->constraint);
    }

    public function testDisabledCustomerWithoutRole()
    {
        $this->customerUser->expects($this->never())
            ->method('getRoles')
            ->willReturn(null);

        $this->customerUser->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->customerUserCheckRoleValidator->validate($this->customerUser, $this->constraint);
    }
}
