<?php

namespace Oro\Bundle\CommerceMenuBundle\Validator\Constraints;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for PageTarget constraint:
 * - checks that at least one of page target fields are filled
 */
class PageTargetValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof MenuUpdate) {
            throw new \InvalidArgumentException(
                sprintf('Expected "%s", got "%s"', MenuUpdate::class, get_class($value))
            );
        }

        if (!$constraint instanceof PageTarget) {
            throw new \InvalidArgumentException(
                sprintf('Expected constraint of type "%s", got "%s"', PageTarget::class, get_class($constraint))
            );
        }

        if ($value->isDivider()) {
            // Divider cannot have a target.
            return;
        }

        if (!$value->isCustom()) {
            // Non-custom menu update are allowed to have empty target.
            return;
        }

        if (!$value->getTargetType()) {
            $this->context
                ->buildViolation($constraint->contentNodeEmpty)
                ->atPath('contentNode')
                ->addViolation();
            $this->context
                ->buildViolation($constraint->systemPageRouteEmpty)
                ->atPath('systemPageRoute')
                ->addViolation();
            $this->context
                ->buildViolation($constraint->uriEmpty)
                ->atPath('uri')
                ->addViolation();
        }
    }
}
