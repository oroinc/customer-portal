<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\AccessRule;

use Oro\Bundle\CustomerBundle\Acl\AccessRule\SelfManagedPublicCustomerUserRoleAccessRule;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\SecurityBundle\AccessRule\Criteria;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\Comparison;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\CompositeExpression;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\NullComparison;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\Path;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AccessRuleWalker;

class SelfManagedPublicCustomerUserRoleAccessRuleTest extends \PHPUnit\Framework\TestCase
{
    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var SelfManagedPublicCustomerUserRoleAccessRule */
    private $rule;

    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->rule = new SelfManagedPublicCustomerUserRoleAccessRule($this->tokenAccessor);
    }

    public function testIsApplicable()
    {
        $this->assertTrue($this->rule->isApplicable($this->createMock(Criteria::class)));
    }

    public function testProcessOnEmptyExistingExpression()
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, CustomerUserRole::class, 'e');

        $this->rule->process($criteria);

        $this->assertEquals(
            new CompositeExpression(
                CompositeExpression::TYPE_AND,
                [
                    new Comparison(new Path('selfManaged'), Comparison::EQ, true),
                    new Comparison(new Path('public'), Comparison::EQ, true)
                ]
            ),
            $criteria->getExpression()
        );
    }

    public function testProcessOnNotViewPermission()
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, CustomerUserRole::class, 'e', 'EDIT');
        $criteria->andExpression(new Comparison(new Path('id'), Comparison::GT, 0));

        $this->rule->process($criteria);

        $this->assertEquals(
            new CompositeExpression(
                CompositeExpression::TYPE_AND,
                [
                    new CompositeExpression(
                        CompositeExpression::TYPE_AND,
                        [
                            new Comparison(new Path('id'), Comparison::GT, 0),
                            new Comparison(new Path('selfManaged'), Comparison::EQ, true),

                        ]
                    ),
                    new Comparison(new Path('public'), Comparison::EQ, true)
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
                            new Comparison(new Path('id'), Comparison::GT, 0),
                            new CompositeExpression(
                                CompositeExpression::TYPE_AND,
                                [
                                    new NullComparison(new Path('customer'))
                                ]
                            )
                        ]
                    )
                ]
            ),
            $criteria->getExpression()
        );
    }

    public function testProcessWithOrganizationId()
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getOrganizationId')
            ->willReturn($organizationId = 10);

        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, CustomerUserRole::class, 'e');
        $criteria->andExpression(new Comparison(new Path('id'), Comparison::GT, 0));

        $this->rule->process($criteria);

        $this->assertEquals(
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
                            new Comparison(new Path('id'), Comparison::GT, 0),
                            new CompositeExpression(
                                CompositeExpression::TYPE_AND,
                                [
                                    new NullComparison(new Path('customer')),
                                    new Comparison(new Path('organization'), Comparison::EQ, $organizationId)
                                ]
                            )
                        ]
                    )
                ]
            ),
            $criteria->getExpression()
        );
    }
}
