<?php

namespace Oro\Bundle\CustomerBundle\Acl\AccessRule;

use Oro\Bundle\SecurityBundle\AccessRule\AccessRuleInterface;
use Oro\Bundle\SecurityBundle\AccessRule\Criteria;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\Comparison;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\CompositeExpression;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\NullComparison;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\Path;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * The access rule that allows the access only to public self managed customer user roles.
 */
class SelfManagedPublicCustomerUserRoleAccessRule implements AccessRuleInterface
{
    /** @var TokenAccessorInterface */
    private $tokenAccessor;

    public function __construct(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(Criteria $criteria): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Criteria $criteria): void
    {
        if ($criteria->getPermission() === 'VIEW' && $criteria->getExpression()) {
            $this->processViewPermission($criteria);
        } else {
            // Adds (selfManaged = TRUE AND public = TRUE) expressions
            $criteria->andExpression(new Comparison(new Path('selfManaged'), Comparison::EQ, true));
            $criteria->andExpression(new Comparison(new Path('public'), Comparison::EQ, true));
        }
    }

    /**
     * Changes the criteria expression to:
     * (selfManaged = TRUE AND public = TRUE)
     * AND
     * ({previous expression} OR (customer IS NULL AND organization = {organizationId}))
     */
    private function processViewPermission(Criteria $criteria): void
    {
        $notAssignedRolesExpressions[] = new NullComparison(new Path('customer'));
        $organizationId = $this->tokenAccessor->getOrganizationId();
        if ($organizationId) {
            $notAssignedRolesExpressions[] = new Comparison(
                new Path('organization'),
                Comparison::EQ,
                $organizationId
            );
        }

        $criteria->setExpression(
            new CompositeExpression(
                CompositeExpression::TYPE_AND,
                [
                    new CompositeExpression(
                        CompositeExpression::TYPE_AND,
                        [
                            new Comparison(new Path('selfManaged'), Comparison::EQ, true),
                            new Comparison(new Path('public'), Comparison::EQ, true),
                        ]
                    ),
                    new CompositeExpression(
                        CompositeExpression::TYPE_OR,
                        [
                            $criteria->getExpression(),
                            new CompositeExpression(
                                CompositeExpression::TYPE_AND,
                                $notAssignedRolesExpressions
                            )
                        ]
                    )
                ]
            )
        );
    }
}
