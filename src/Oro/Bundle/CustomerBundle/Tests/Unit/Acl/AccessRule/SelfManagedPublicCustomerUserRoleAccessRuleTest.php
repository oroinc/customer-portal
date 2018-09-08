<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\AccessRule;

use Oro\Bundle\CustomerBundle\Acl\AccessRule\SelfManagedPublicCustomerUserRoleAccessRule;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\SecurityBundle\AccessRule\Criteria;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\Comparison;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\CompositeExpression;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\NullComparison;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\Path;
use Oro\Bundle\SecurityBundle\ORM\Walker\AccessRuleWalker;
use PHPUnit\Framework\TestCase;

class SelfManagedPublicCustomerUserRoleAccessRuleTest extends TestCase
{
    /** @var SelfManagedPublicCustomerUserRoleAccessRule */
    private $rule;

    protected function setUp()
    {
        $this->rule = new SelfManagedPublicCustomerUserRoleAccessRule();
    }

    public function testIsApplicableWithoutOptionsInCriteriaAndNotSupportedEntity()
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, \stdClass::class, 'e');

        $this->assertFalse($this->rule->isApplicable($criteria));
    }

    public function testIsApplicableWithOptionsInCriteriaAndNotSupportedEntity()
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, \stdClass::class, 'e');
        $criteria->setOption(SelfManagedPublicCustomerUserRoleAccessRule::ENABLE_RULE, true);

        $this->assertFalse($this->rule->isApplicable($criteria));
    }

    public function testIsApplicableWithoutOptionsInCriteriaAndSupportedEntity()
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, CustomerUserRole::class, 'e');

        $this->assertFalse($this->rule->isApplicable($criteria));
    }

    public function testIsApplicableWithOptionsInCriteriaAndSupportedEntity()
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, CustomerUserRole::class, 'e');
        $criteria->setOption(SelfManagedPublicCustomerUserRoleAccessRule::ENABLE_RULE, true);

        $this->assertTrue($this->rule->isApplicable($criteria));
    }

    public function testProcess()
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, CustomerUserRole::class, 'e');

        $this->rule->process($criteria);

        $this->assertEquals(
            new CompositeExpression(
                CompositeExpression::TYPE_AND,
                [
                    new Comparison(new Path('selfManaged'), Comparison::EQ, true),
                    new Comparison(new Path('public'), Comparison::EQ, true),
                    new NullComparison(new Path('customer')),
                ]
            ),
            $criteria->getExpression()
        );
    }

    public function testProcessWithExistingExpressionInCriteria()
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, CustomerUserRole::class, 'e');
        $criteria->andExpression(new Comparison(new Path('id'), Comparison::GT, 0));

        $this->rule->process($criteria);

        $this->assertEquals(
            new CompositeExpression(
                CompositeExpression::TYPE_OR,
                [
                    new Comparison(new Path('id'), Comparison::GT, 0),
                    new CompositeExpression(
                        CompositeExpression::TYPE_AND,
                        [
                            new Comparison(new Path('selfManaged'), Comparison::EQ, true),
                            new Comparison(new Path('public'), Comparison::EQ, true),
                            new NullComparison(new Path('customer')),
                        ]
                    )
                ]
            ),
            $criteria->getExpression()
        );
    }
}
