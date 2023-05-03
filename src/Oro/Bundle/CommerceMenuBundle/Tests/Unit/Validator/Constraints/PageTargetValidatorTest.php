<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Entity\Stub\MenuUpdateStub;
use Oro\Bundle\CommerceMenuBundle\Validator\Constraints\PageTarget;
use Oro\Bundle\CommerceMenuBundle\Validator\Constraints\PageTargetValidator;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class PageTargetValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): PageTargetValidator
    {
        return new PageTargetValidator();
    }

    public function testValidateWhenInvalidValue(): void
    {
        $value = new \stdClass();
        $this->expectExceptionObject(new UnexpectedValueException($value, MenuUpdate::class));

        $this->validator->validate($value, $this->createMock(Constraint::class));
    }

    public function testValidateWhenInvalidConstraint(): void
    {
        $constraint = new NotBlank();
        $this->expectExceptionObject(new UnexpectedTypeException($constraint, PageTarget::class));

        $this->validator->validate($this->createMock(MenuUpdate::class), $constraint);
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

    public function testValidateWhenNotInForm(): void
    {
        $constraint = new PageTarget();
        $this->validator->validate(new MenuUpdateStub(), $constraint);

        $this->assertNoViolation();
    }

    public function testValidateWhenTargetTypeNone(): void
    {
        $constraint = new PageTarget();
        $this->root = $this->createMock(FormInterface::class);
        $targetTypeForm = $this->createMock(FormInterface::class);
        $targetTypeForm
            ->expects(self::once())
            ->method('getData')
            ->willReturn(MenuUpdate::TARGET_NONE);
        $this->root
            ->expects(self::once())
            ->method('has')
            ->with('targetType')
            ->willReturn(true);
        $this->root
            ->expects(self::once())
            ->method('get')
            ->with('targetType')
            ->willReturn($targetTypeForm);

        $this->context = $this->createContext();
        $this->validator->initialize($this->context);
        $this->validator->validate(new MenuUpdateStub(), $constraint);

        $this->assertNoViolation();
    }

    public function testValidateWhenTargetTypeContentNode(): void
    {
        $constraint = new PageTarget();
        $this->root = $this->createMock(FormInterface::class);
        $targetTypeForm = $this->createMock(FormInterface::class);
        $targetTypeForm
            ->expects(self::once())
            ->method('getData')
            ->willReturn(MenuUpdate::TARGET_CONTENT_NODE);
        $this->root
            ->expects(self::once())
            ->method('has')
            ->with('targetType')
            ->willReturn(true);
        $this->root
            ->expects(self::once())
            ->method('get')
            ->with('targetType')
            ->willReturn($targetTypeForm);

        $this->context = $this->createContext();
        $this->validator->initialize($this->context);
        $this->validator->validate(new MenuUpdateStub(), $constraint);

        $this
            ->buildViolation($constraint->contentNodeEmpty)
            ->atPath('property.path.contentNode')
            ->assertRaised();
    }

    public function testValidateWhenTargetTypeCategory(): void
    {
        $constraint = new PageTarget();
        $this->root = $this->createMock(FormInterface::class);
        $targetTypeForm = $this->createMock(FormInterface::class);
        $targetTypeForm
            ->expects(self::once())
            ->method('getData')
            ->willReturn(MenuUpdate::TARGET_CATEGORY);
        $this->root
            ->expects(self::once())
            ->method('has')
            ->with('targetType')
            ->willReturn(true);
        $this->root
            ->expects(self::once())
            ->method('get')
            ->with('targetType')
            ->willReturn($targetTypeForm);

        $this->context = $this->createContext();
        $this->validator->initialize($this->context);
        $this->validator->validate(new MenuUpdateStub(), $constraint);

        $this
            ->buildViolation($constraint->categoryEmpty)
            ->atPath('property.path.category')
            ->assertRaised();
    }

    public function testValidateWhenTargetTypeSystemPage(): void
    {
        $constraint = new PageTarget();
        $this->root = $this->createMock(FormInterface::class);
        $targetTypeForm = $this->createMock(FormInterface::class);
        $targetTypeForm
            ->expects(self::once())
            ->method('getData')
            ->willReturn(MenuUpdate::TARGET_SYSTEM_PAGE);
        $this->root
            ->expects(self::once())
            ->method('has')
            ->with('targetType')
            ->willReturn(true);
        $this->root
            ->expects(self::once())
            ->method('get')
            ->with('targetType')
            ->willReturn($targetTypeForm);

        $this->context = $this->createContext();
        $this->validator->initialize($this->context);
        $this->validator->validate(new MenuUpdateStub(), $constraint);

        $this
            ->buildViolation($constraint->systemPageRouteEmpty)
            ->atPath('property.path.systemPageRoute')
            ->assertRaised();
    }

    public function testValidateWhenTargetTypeUri(): void
    {
        $constraint = new PageTarget();
        $this->root = $this->createMock(FormInterface::class);
        $targetTypeForm = $this->createMock(FormInterface::class);
        $targetTypeForm
            ->expects(self::once())
            ->method('getData')
            ->willReturn(MenuUpdate::TARGET_URI);
        $this->root
            ->expects(self::once())
            ->method('has')
            ->with('targetType')
            ->willReturn(true);
        $this->root
            ->expects(self::once())
            ->method('get')
            ->with('targetType')
            ->willReturn($targetTypeForm);

        $this->context = $this->createContext();
        $this->validator->initialize($this->context);
        $this->validator->validate(new MenuUpdateStub(), $constraint);

        $this
            ->buildViolation($constraint->uriEmpty)
            ->atPath('property.path.uri')
            ->assertRaised();
    }

    /**
     * @dataProvider targetTypeDataProvider
     */
    public function testValidate(string $targetType): void
    {
        $constraint = new PageTarget();
        $this->root = $this->createMock(FormInterface::class);
        $targetTypeForm = $this->createMock(FormInterface::class);
        $targetTypeForm
            ->expects(self::once())
            ->method('getData')
            ->willReturn($targetType);
        $this->root
            ->expects(self::once())
            ->method('has')
            ->with('targetType')
            ->willReturn(true);
        $this->root
            ->expects(self::once())
            ->method('get')
            ->with('targetType')
            ->willReturn($targetTypeForm);

        $this->context = $this->createContext();
        $this->validator->initialize($this->context);
        $menuUpdate = (new MenuUpdateStub())
            ->setContentNode($this->createMock(ContentNode::class))
            ->setCategory($this->createMock(Category::class))
            ->setSystemPageRoute('sample_route')
            ->setUri('/sample/uri/');

        $this->validator->validate($menuUpdate, $constraint);

        $this->assertNoViolation();
    }

    public function targetTypeDataProvider(): array
    {
        return [
            [MenuUpdate::TARGET_CONTENT_NODE],
            [MenuUpdate::TARGET_CATEGORY],
            [MenuUpdate::TARGET_SYSTEM_PAGE],
            [MenuUpdate::TARGET_URI],
            [MenuUpdate::TARGET_NONE],
        ];
    }
}
