<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener\Datagrid;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\CustomerBundle\Acl\AccessRule\SelfManagedPublicCustomerUserRoleAccessRule;
use Oro\Bundle\CustomerBundle\EventListener\Datagrid\CustomerUserRoleDatagridListener;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\OrmResultBefore;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class CustomerUserRoleDatagridListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CustomerUserRoleDatagridListener
     */
    protected $listener;

    /**
     * @var QueryBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $queryBuilder;

    /**
     * @var AclHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $aclHelper;

    protected function setUp()
    {
        $this->queryBuilder = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();
        $this->aclHelper = $this->createAclHelperMock();
        $this->listener = new CustomerUserRoleDatagridListener($this->aclHelper);
    }

    protected function tearDown()
    {
        unset($this->listener, $this->aclHelper);
    }

    public function testOnResultBefore()
    {
        $query = $this->createMock(AbstractQuery::class);
        $event = new OrmResultBefore($this->createMock(DatagridInterface::class), $query);
        $this->aclHelper->expects($this->once())
            ->method('apply')
            ->with(
                $query,
                'VIEW',
                [SelfManagedPublicCustomerUserRoleAccessRule::ENABLE_RULE => true]
            );

        $this->listener->onResultBefore($event);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createAclHelperMock()
    {
        return $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
