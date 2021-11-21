<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\CustomerBundle\Validator\Constraints\EmailCaseInsensitiveOption;
use Oro\Bundle\CustomerBundle\Validator\Constraints\EmailCaseInsensitiveOptionValidator;
use Oro\Bundle\DataGridBundle\Tools\DatagridRouteHelper;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\FilterBundle\Grid\Extension\AbstractFilterExtension;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailCaseInsensitiveOptionValidatorTest extends ConstraintValidatorTestCase
{
    /** @var CustomerUserRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $userRepository;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var DatagridRouteHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $datagridRouteHelper;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(CustomerUserRepository::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->datagridRouteHelper = $this->createMock(DatagridRouteHelper::class);
        parent::setUp();
    }

    protected function createValidator()
    {
        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->any())
            ->method('getRepository')
            ->with(CustomerUser::class)
            ->willReturn($this->userRepository);

        return new EmailCaseInsensitiveOptionValidator(
            $doctrine,
            $this->translator,
            $this->datagridRouteHelper
        );
    }

    public function testValidateExceptions()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            sprintf('Expected argument of type "%s"', EmailCaseInsensitiveOption::class)
        );

        $this->validator->validate('', $this->createMock(Constraint::class));
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

        $constraint = new EmailCaseInsensitiveOption();
        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    public function validateValidDataProvider(): array
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
        $constraint = new EmailCaseInsensitiveOption();

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
            ->with($constraint->clickHere, [], 'validators')
            ->willReturnArgument(0);

        $this->validator->validate(true, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('%click_here%', sprintf('<a href="some/link/to/grid">%s</a>', $constraint->clickHere))
            ->setInvalidValue(true)
            ->assertRaised();
    }
}
