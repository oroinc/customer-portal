<?php

namespace Oro\Bundle\CommerceMenuBundle\Validator\Constraints;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Validator for PageTarget constraint:
 * - checks that the chosen page target field is filled
 */
class PageTargetValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof MenuUpdate) {
            throw new UnexpectedValueException($value, MenuUpdate::class);
        }

        if (!$constraint instanceof PageTarget) {
            throw new UnexpectedTypeException($constraint, PageTarget::class);
        }

        if ($value->isDivider()) {
            // Divider cannot have a target.
            return;
        }

        if (!$this->context->getRoot() instanceof FormInterface) {
            // Target type cannot be detected when not in the form context.
            return;
        }

        if (!$this->context->getRoot()->has('targetType')) {
            return;
        }

        $this->validateTargetFields($value, $constraint);
    }

    private function validateTargetFields(MenuUpdate $value, PageTarget $constraint): void
    {
        $targetType = $this->context->getRoot()->get('targetType')->getData();
        switch ($targetType) {
            case MenuUpdate::TARGET_CONTENT_NODE:
                if ($value->getContentNode() === null) {
                    $this->context
                        ->buildViolation($constraint->contentNodeEmpty)
                        ->atPath('contentNode')
                        ->addViolation();
                }
                break;

            case MenuUpdate::TARGET_CATEGORY:
                if ($value->getCategory() === null) {
                    $this->context
                        ->buildViolation($constraint->categoryEmpty)
                        ->atPath('category')
                        ->addViolation();
                }
                break;

            case MenuUpdate::TARGET_SYSTEM_PAGE:
                if (!$value->getSystemPageRoute()) {
                    $this->context
                        ->buildViolation($constraint->systemPageRouteEmpty)
                        ->atPath('systemPageRoute')
                        ->addViolation();
                }
                break;

            case MenuUpdate::TARGET_URI:
                if (!$value->getUri()) {
                    $this->context
                        ->buildViolation($constraint->uriEmpty)
                        ->atPath('uri')
                        ->addViolation();
                }
                break;
        }
    }
}
