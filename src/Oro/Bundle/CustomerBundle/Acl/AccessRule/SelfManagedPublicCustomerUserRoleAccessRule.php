<?php

namespace Oro\Bundle\CustomerBundle\Acl\AccessRule;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\SecurityBundle\AccessRule\AccessRuleInterface;
use Oro\Bundle\SecurityBundle\AccessRule\Criteria;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\Comparison;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\CompositeExpression;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\NullComparison;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\Path;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * The access rule that adds "selfManaged = TRUE AND public = TRUE AND customer is NULL" expression
 * by OR operator for CustomerUserRole entity.
 * Adds additional check for organization if user is authenticated
 */
class SelfManagedPublicCustomerUserRoleAccessRule implements AccessRuleInterface
{
    /** The option that allows to enable the rule. Default value is false.  */
    public const ENABLE_RULE = 'selfManagedPublicCustomerUserRoleEnable';

    /**
     * @var TokenAccessorInterface
     */
    private $tokenAccessor;

    /**
     * @param TokenAccessorInterface $tokenAccessor
     */
    public function __construct(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }

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
        $expressions = [
            new Comparison(new Path('selfManaged'), Comparison::EQ, true),
            new Comparison(new Path('public'), Comparison::EQ, true),
            new NullComparison(new Path('customer')),
        ];

        $organizationId = $this->tokenAccessor->getOrganizationId();
        if ($organizationId) {
            // Adds expression to the beginning of array because it would not work in the end due to the specialty of
            // NullComparison expression which causes AstVisitor to clear alias. See AstVisitor::walkNullComparison().
            array_unshift($expressions, new Comparison(new Path('organization'), Comparison::EQ, $organizationId));
        }

        $criteria->orExpression(new CompositeExpression(CompositeExpression::TYPE_AND, $expressions));
    }
}
