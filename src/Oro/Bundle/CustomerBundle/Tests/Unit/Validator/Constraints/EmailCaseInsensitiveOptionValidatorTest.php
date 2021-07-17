<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\CustomerBundle\Validator\Constraints\EmailCaseInsensitiveOptionConstraint;
use Oro\Bundle\CustomerBundle\Validator\Constraints\EmailCaseInsensitiveOptionValidator;
use Oro\Bundle\DataGridBundle\Tools\DatagridRouteHelper;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\FilterBundle\Grid\Extension\AbstractFilterExtension;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailCaseInsensitiveOptionValidatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerUserRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $userRepository;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var DatagridRouteHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $datagridRouteHelper;

    /** @var EmailCaseInsensitiveOptionValidator */
    private $validator;

    /** @var EmailCaseInsensitiveOptionConstraint */
    private $constraint;

    /** @var ConstraintViolationBuilderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $violationBuilder;

    /** @var ExecutionContextInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $executionContext;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(CustomerUserRepository::class);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $doctrine->expects($this->any())
            ->method('getManagerForClass')
            ->with(CustomerUser::class)
            ->willReturn($em);
        $em->expects($this->any())
            ->method('getRepository')
            ->with(CustomerUser::class)
            ->willReturn($this->userRepository);

        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->datagridRouteHelper = $this->createMock(DatagridRouteHelper::class);

        $this->validator = new EmailCaseInsensitiveOptionValidator(
            $doctrine,
            $this->translator,
            $this->datagridRouteHelper
        );

        $this->constraint = new EmailCaseInsensitiveOptionConstraint();

        $this->violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->violationBuilder->expects($this->any())->method('setInvalidValue')->willReturnSelf();
        $this->violationBuilder->expects($this->any())->method('addViolation')->willReturnSelf();

        $this->executionContext = $this->createMock(ExecutionContextInterface::class);
    }

    public function testValidateExceptions()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            sprintf('Expected argument of type "%s"', EmailCaseInsensitiveOptionConstraint::class)
        );

        /** @var Constraint $constraint */
        $constraint = $this->createMock(Constraint::class);

        $this->validator->initialize($this->executionContext);
        $this->validator->validate('', $constraint);
    }

    /**
     * @dataProvider validateValidDataProvider
     */
    public function testValidateValid(bool $value)
    {
        $this->userRepository->expects($this->any())
            ->method('findLowercaseDuplicatedEmails')
            ->with(10)
            ->willReturn([]);

        $this->executionContext->expects($this->never())
            ->method('buildViolation');

        $this->validator->initialize($this->executionContext);
        $this->validator->validate($value, $this->constraint);
    }

    /**
     * @return array
     */
    public function validateValidDataProvider()
    {
        return [
            [
                'value' => false,
            ],
            [
                'value' => true,
            ],
        ];
    }

    public function testValidateInvalidDuplicatedEmails()
    {
        $this->userRepository->expects($this->once())
            ->method('findLowercaseDuplicatedEmails')
            ->with(10)
            ->willReturn(['test@example.com']);

        $this->datagridRouteHelper->expects($this->once())
            ->method('generate')
            ->with(
                'oro_customer_customer_user_index',
                'customer-customer-user-grid',
                [
                    AbstractFilterExtension::MINIFIED_FILTER_PARAM => [
                        'email' => [
                            'type' => TextFilterType::TYPE_IN,
                            'value' => implode(',', ['test@example.com']),
                        ]
                    ]
                ]
            )
            ->willReturn('some/link/to/grid');

        $this->translator->expects($this->once())
            ->method('trans')
            ->with($this->constraint->clickHere, [], 'validators')
            ->willReturnArgument(0);

        $this->executionContext->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->message)
            ->willReturn($this->violationBuilder);

        $this->violationBuilder->expects($this->once())
            ->method('setParameters')
            ->with(
                [
                    '%click_here%' => sprintf('<a href="some/link/to/grid">%s</a>', $this->constraint->clickHere)
                ]
            )
            ->willReturnSelf();

        $this->validator->initialize($this->executionContext);
        $this->validator->validate(true, $this->constraint);
    }
}
