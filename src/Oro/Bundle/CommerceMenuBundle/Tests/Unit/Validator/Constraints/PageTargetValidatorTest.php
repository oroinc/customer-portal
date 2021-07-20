<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Validator\Constraints\PageTarget;
use Oro\Bundle\CommerceMenuBundle\Validator\Constraints\PageTargetValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class PageTargetValidatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var PageTargetValidator */
    private $validator;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ExecutionContextInterface */
    private $context;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);

        $this->validator = new PageTargetValidator();
        $this->validator->initialize($this->context);
    }

    public function testValidateWhenInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected "Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate", got "stdClass"');

        $this->validator->validate(new \stdClass(), $this->createMock(Constraint::class));
    }

    public function testValidateWhenInvalidConstraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Expected constraint of type "Oro\Bundle\CommerceMenuBundle\Validator\Constraints\PageTarget", got '
            . '"Symfony\Component\Validator\Constraints\NotBlank"'
        );

        $this->validator->validate($this->createMock(MenuUpdate::class), new NotBlank());
    }

    public function testValidateWhenDivider(): void
    {
        $menuUpdate = $this->createMock(MenuUpdate::class);
        $menuUpdate
            ->expects($this->once())
            ->method('isDivider')
            ->willReturn(true);

        $this->context
            ->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($menuUpdate, new PageTarget());
    }

    public function testValidateWhenNoTargetType(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context
            ->expects($this->exactly(3))
            ->method('buildViolation')
            ->willReturnMap(
                [
                    [
                        'oro.commercemenu.validator.menu_update.content_node_empty.message',
                        [],
                        $contentNodeViolationBuilder = clone $violationBuilder,
                    ],
                    [
                        'oro.commercemenu.validator.menu_update.system_page_route_empty.message',
                        [],
                        $systemPageViolationBuilder = clone $violationBuilder,
                    ],
                    [
                        'oro.commercemenu.validator.menu_update.uri_empty.message',
                        [],
                        $uriViolationBuilder = clone $violationBuilder,
                    ],
                ]
            );

        $this->mockValidationBuilder($contentNodeViolationBuilder, 'contentNode');
        $this->mockValidationBuilder($systemPageViolationBuilder, 'systemPageRoute');
        $this->mockValidationBuilder($uriViolationBuilder, 'uri');

        $this->validator->validate((new MenuUpdate())->setCustom(true), new PageTarget());
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $violationBuilder
     * @param string $path
     */
    private function mockValidationBuilder($violationBuilder, string $path): void
    {
        $violationBuilder
            ->expects($this->once())
            ->method('atPath')
            ->with($path)
            ->willReturnSelf();

        $violationBuilder
            ->expects($this->once())
            ->method('addViolation');
    }

    /**
     * @dataProvider validateDataProvider
     */
    public function testValidate(bool $isCustom): void
    {
        $this->context
            ->expects($this->never())
            ->method('buildViolation');

        $menuUpdate = new MenuUpdate();
        $menuUpdate->setUri('sample/uri');
        $menuUpdate->setCustom($isCustom);

        $this->validator->validate($menuUpdate, new PageTarget());
    }

    public function validateDataProvider(): array
    {
        return [
            ['isCustom' => true],
            ['isCustom' => false],
        ];
    }
}
