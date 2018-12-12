<?php

namespace Oro\Bundle\CustomerBundle\Acl\AccessRule;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
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
 * Adds additional check for organization if user is authenticated.
 */
class SelfManagedPublicCustomerUserRoleAccessRule implements AccessRuleInterface
{
    /** @var TokenAccessorInterface */
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
            $criteria->getEntityClass() === CustomerUserRole::class
            && $this->tokenAccessor->getUser() instanceof CustomerUser;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Criteria $criteria): void
    {
        $expressions = [
            new Comparison(new Path('selfManaged'), Comparison::EQ, true),
            new Comparison(new Path('public'), Comparison::EQ, true),
            new NullComparison(new Path('customer'))
        ];

        $organizationId = $this->tokenAccessor->getOrganizationId();
        if ($organizationId) {
            $expressions[] = new Comparison(new Path('organization'), Comparison::EQ, $organizationId);
        }

        $criteria->orExpression(new CompositeExpression(CompositeExpression::TYPE_AND, $expressions));
    }
}
