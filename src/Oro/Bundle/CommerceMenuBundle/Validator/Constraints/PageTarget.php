<?php

namespace Oro\Bundle\CommerceMenuBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for PageTarget.
 */
class PageTarget extends Constraint
{
    /** @var string */
    public $contentNodeEmpty = 'oro.commercemenu.validator.menu_update.content_node_empty.message';

    /** @var string */
    public $systemPageRouteEmpty = 'oro.commercemenu.validator.menu_update.system_page_route_empty.message';

    /** @var string */
    public $uriEmpty = 'oro.commercemenu.validator.menu_update.uri_empty.message';

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
