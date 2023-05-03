<?php

namespace Oro\Bundle\CommerceMenuBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for PageTarget.
 */
class PageTarget extends Constraint
{
    public string $contentNodeEmpty = 'oro.commercemenu.validator.menu_update.content_node_empty.message';

    public string $systemPageRouteEmpty = 'oro.commercemenu.validator.menu_update.system_page_route_empty.message';

    public string $uriEmpty = 'oro.commercemenu.validator.menu_update.uri_empty.message';

    public string $categoryEmpty = 'oro.commercemenu.validator.menu_update.category_empty.message';

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return PageTargetValidator::class;
    }
}
