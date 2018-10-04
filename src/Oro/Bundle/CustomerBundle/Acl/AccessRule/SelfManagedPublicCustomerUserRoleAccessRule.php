<?php

namespace Oro\Bundle\CustomerBundle\Acl\AccessRule;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\SecurityBundle\AccessRule\AccessRuleInterface;
use Oro\Bundle\SecurityBundle\AccessRule\Criteria;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\Comparison;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\CompositeExpression;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\NullComparison;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\Path;

/**
 * The access rule that adds "selfManaged = TRUE AND public = TRUE AND customer is NULL" expression
 * by OR operator for CustomerUserRole entity.
 */
class SelfManagedPublicCustomerUserRoleAccessRule implements AccessRuleInterface
{
    /** The option that allows to enable the rule. Default value is false.  */
    public const ENABLE_RULE = 'selfManagedPublicCustomerUserRoleEnable';

    /**
     * {@inheritdoc}
     */
    public function isApplicable(Criteria $criteria): bool
    {
        return
            $criteria->getOption(self::ENABLE_RULE, false)
            && $criteria->getEntityClass() === CustomerUserRole::class;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Criteria $criteria): void
    {
        $criteria->orExpression(
            new CompositeExpression(
                CompositeExpression::TYPE_AND,
                [
                    new Comparison(new Path('selfManaged'), Comparison::EQ, true),
                    new Comparison(new Path('public'), Comparison::EQ, true),
                    new NullComparison(new Path('customer')),
                ]
            )
        );
    }
}
