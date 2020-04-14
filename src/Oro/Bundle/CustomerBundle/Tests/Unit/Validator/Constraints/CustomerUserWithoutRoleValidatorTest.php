<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerUserWithoutRole;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerUserWithoutRoleValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CustomerUserWithoutRoleValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CustomerUserWithoutRoleValidator
     */
    protected $validator;

    /**
     * @var CustomerUserWithoutRole
     */
    protected $constraint;

    /**
     * @var ExecutionContextInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    protected function setUp(): void
    {
        $this->constraint = new CustomerUserWithoutRole();
        $this->validator = new CustomerUserWithoutRoleValidator();

        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);
    }

    public function testDisabledCustomerWithoutRole()
    {
        $this->context->expects($this->never())->method('addViolation');
        $user = (new CustomerUser())->setEnabled(false);
        $this->validator->validate(new ArrayCollection([$user]), $this->constraint);
    }

    public function testEnabledUserWithoutRoles()
    {
        $user = (new CustomerUser())
            ->setFirstName('John')
            ->setLastName('Rembo')
            ->setEnabled(true);

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($this->constraint->message, ['{{ userName }}' => 'John Rembo']);

        $this->validator->validate(new ArrayCollection([$user]), $this->constraint);
    }
}
