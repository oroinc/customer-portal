<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Validator\Constraints\PageTarget;
use Oro\Bundle\CommerceMenuBundle\Validator\Constraints\PageTargetValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class PageTargetValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new PageTargetValidator();
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
        $menuUpdate->expects($this->once())
            ->method('isDivider')
            ->willReturn(true);

        $this->validator->validate($menuUpdate, new PageTarget());

        $this->assertNoViolation();
    }

    public function testValidateWhenNoTargetType(): void
    {
        $constraint = new PageTarget();
        $this->validator->validate((new MenuUpdate())->setCustom(true), $constraint);

        $this
            ->buildViolation($constraint->contentNodeEmpty)
            ->atPath('property.path.contentNode')
            ->buildNextViolation($constraint->systemPageRouteEmpty)
            ->atPath('property.path.systemPageRoute')
            ->buildNextViolation($constraint->uriEmpty)
            ->atPath('property.path.uri')
            ->assertRaised();
    }

    /**
     * @dataProvider validateDataProvider
     */
    public function testValidate(bool $isCustom): void
    {
        $menuUpdate = new MenuUpdate();
        $menuUpdate->setUri('sample/uri');
        $menuUpdate->setCustom($isCustom);

        $this->validator->validate($menuUpdate, new PageTarget());

        $this->assertNoViolation();
    }

    public function validateDataProvider(): array
    {
        return [
            ['isCustom' => true],
            ['isCustom' => false],
        ];
    }
}
