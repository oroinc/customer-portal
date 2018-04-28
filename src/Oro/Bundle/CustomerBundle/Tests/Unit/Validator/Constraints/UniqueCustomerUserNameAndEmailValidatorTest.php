<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Validator\Constraints\UniqueCustomerUserNameAndEmail;
use Oro\Bundle\CustomerBundle\Validator\Constraints\UniqueCustomerUserNameAndEmailValidator;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueCustomerUserNameAndEmailValidatorTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /** @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $customerUserRepository;

    /** @var UniqueCustomerUserNameAndEmailValidator */
    private $validator;

    protected function setUp()
    {
        $this->customerUserRepository = $this->createMock(EntityRepository::class);
        $this->validator = new UniqueCustomerUserNameAndEmailValidator($this->customerUserRepository);
    }

    public function testValidationSucceedsWhenUsersWithSuchEmailOrUsernameDoesntExist()
    {
        $newCustomer = $this->getEntity(
            CustomerUser::class,
            [
                'username' => 'foo',
                'email' => 'foo',
            ]
        );

        $this->customerUserRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'foo', 'isGuest' => false])
            ->willReturn(null);

        /** @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject $context */
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->never())->method('buildViolation');

        /** @var UniqueCustomerUserNameAndEmail|\PHPUnit_Framework_MockObject_MockObject $constraint */
        $constraint = $this->createMock(UniqueCustomerUserNameAndEmail::class);

        $this->validator->initialize($context);
        $this->validator->validate($newCustomer, $constraint);
    }

    public function testValidationSucceedsWhenOnlyGuestUsersExistWithSuchUsernameOrEmail()
    {
        $newCustomer = $this->getEntity(
            CustomerUser::class,
            [
                'username' => 'foo',
                'email' => 'foo',
            ]
        );

        $this->customerUserRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'foo', 'isGuest' => false])
            ->willReturn(null);

        /** @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject $context */
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->never())->method('buildViolation');

        /** @var UniqueCustomerUserNameAndEmail|\PHPUnit_Framework_MockObject_MockObject $constraint */
        $constraint = $this->createMock(UniqueCustomerUserNameAndEmail::class);

        $this->validator->initialize($context);
        $this->validator->validate($newCustomer, $constraint);
    }

    public function testValidationSucceedsWhenGuestUsersIsPassed()
    {
        $newCustomer = new CustomerUser();
        $newCustomer->setIsGuest(true);

        $this->customerUserRepository->expects($this->never())->method('findOneBy');

        /** @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject $context */
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->never())->method('buildViolation');

        /** @var UniqueCustomerUserNameAndEmail|\PHPUnit_Framework_MockObject_MockObject $constraint */
        $constraint = $this->createMock(UniqueCustomerUserNameAndEmail::class);

        $this->validator->initialize($context);
        $this->validator->validate($newCustomer, $constraint);
    }

    /**
     * @dataProvider guestCustomerUsersDataProvider
     *
     * @param EntityTrait $existingCustomer
     * @param EntityTrait $newCustomer
     * @param bool $valid
     */
    public function testValidationFailsWhenNonGuestUserWithSuchEmailExists($existingCustomer, $newCustomer, $valid)
    {
        $this->customerUserRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'foo', 'isGuest' => false])
            ->willReturn($existingCustomer);

        /** @var ConstraintViolationBuilderInterface|\PHPUnit_Framework_MockObject_MockObject $violationBuilder */
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        /** @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject $context */
        $context = $this->createMock(ExecutionContextInterface::class);

        if (!$valid) {
            $violationBuilder->expects($this->at(0))->method('atPath')->willReturn($violationBuilder);
            $violationBuilder->expects($this->at(1))->method('setInvalidValue')->willReturn($violationBuilder);
            $violationBuilder->expects($this->at(2))->method('addViolation')->willReturn($violationBuilder);

            $context->expects($this->once())->method('buildViolation')->willReturn($violationBuilder);
        } else {
            $context->expects($this->never())->method('buildViolation');
        }

        /** @var UniqueCustomerUserNameAndEmail|\PHPUnit_Framework_MockObject_MockObject $constraint */
        $constraint = $this->createMock(UniqueCustomerUserNameAndEmail::class);

        $this->validator->initialize($context);
        $this->validator->validate($newCustomer, $constraint);
    }

    /**
     * @return array
     */
    public function guestCustomerUsersDataProvider()
    {
        return [
            'new customer' => [
                $this->getEntity(
                    CustomerUser::class,
                    [
                        'id' => 1,
                        'username' => 'foo',
                        'email' => 'foo',
                        'isGuest' => false,
                    ]
                ),
                $this->getEntity(
                    CustomerUser::class,
                    [
                        'id' => null,
                        'username' => 'foo',
                        'email' => 'foo',
                        'isGuest' => false,
                    ]
                ),
                false
            ],
            'other customer' => [
                $this->getEntity(
                    CustomerUser::class,
                    [
                        'id' => 1,
                        'username' => 'foo',
                        'email' => 'foo',
                        'isGuest' => false,
                    ]
                ),
                $this->getEntity(
                    CustomerUser::class,
                    [
                        'id' => 2,
                        'username' => 'foo',
                        'email' => 'foo',
                        'isGuest' => false,
                    ]
                ),
                false
            ],
            'same customer' => [
                $this->getEntity(
                    CustomerUser::class,
                    [
                        'id' => 1,
                        'username' => 'foo',
                        'email' => 'foo',
                        'isGuest' => false,
                    ]
                ),
                $this->getEntity(
                    CustomerUser::class,
                    [
                        'id' => 1,
                        'username' => 'foo',
                        'email' => 'foo',
                        'isGuest' => false,
                    ]
                ),
                true
            ],
        ];
    }

    public function testValidationCustomerUserAsStringPassedFail()
    {
        $customerUser = new CustomerUser();
        $this->customerUserRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'some@CustomerEmail', 'isGuest' => false])
            ->willReturn($customerUser);

        /** @var ConstraintViolationBuilderInterface|\PHPUnit_Framework_MockObject_MockObject $violationBuilder */
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        /** @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject $context */
        $context = $this->createMock(ExecutionContextInterface::class);

        $violationBuilder->expects($this->at(0))->method('atPath')->willReturn($violationBuilder);
        $violationBuilder->expects($this->at(1))->method('setInvalidValue')->willReturn($violationBuilder);
        $violationBuilder->expects($this->at(2))->method('addViolation')->willReturn($violationBuilder);

        $context->expects($this->once())->method('buildViolation')->willReturn($violationBuilder);

        /** @var UniqueCustomerUserNameAndEmail|\PHPUnit_Framework_MockObject_MockObject $constraint */
        $constraint = $this->createMock(UniqueCustomerUserNameAndEmail::class);

        $this->validator->initialize($context);
        $this->validator->validate('some@CustomerEmail', $constraint);
    }

    public function testValidationCustomerUserAsStringPassedSuccess()
    {
        $this->customerUserRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'some@CustomerEmail', 'isGuest' => false])
            ->willReturn(null);

        /** @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject $context */
        $context = $this->createMock(ExecutionContextInterface::class);

        $context->expects($this->never())->method('buildViolation');


        /** @var UniqueCustomerUserNameAndEmail|\PHPUnit_Framework_MockObject_MockObject $constraint */
        $constraint = $this->createMock(UniqueCustomerUserNameAndEmail::class);

        $this->validator->initialize($context);
        $this->validator->validate('some@CustomerEmail', $constraint);
    }
}
