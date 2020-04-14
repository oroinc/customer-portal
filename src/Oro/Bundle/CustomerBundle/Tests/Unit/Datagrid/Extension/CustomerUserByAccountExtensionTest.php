<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid\Extension;

use Oro\Bundle\CustomerBundle\Datagrid\Extension\CustomerUserByCustomerExtension;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerUserByAccountExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CustomerUserByCustomerExtension
     */
    protected $extension;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Request
     */
    protected $request;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension = new CustomerUserByCustomerExtension();
        /** @var RequestStack|\PHPUnit\Framework\MockObject\MockObject $requestStack */
        $requestStack = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');
        $requestStack->expects($this->any())->method('getCurrentRequest')->willReturn($this->request);
        $this->extension->setRequestStack($requestStack);
        $this->extension->setParameters(new ParameterBag());
    }

    /**
     * @dataProvider isApplicableDataProvider
     * @param string $name
     * @param int|null $customerId
     * @param bool $expected
     */
    public function testIsApplicable($name, $customerId, $expected)
    {
        $this->request->expects($this->any())
            ->method('get')
            ->with(CustomerUserByCustomerExtension::ACCOUNT_KEY)
            ->will($this->returnValue($customerId));

        /** @var \PHPUnit\Framework\MockObject\MockObject|DatagridConfiguration $config */
        $config = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->disableOriginalConstructor()
            ->getMock();
        $config->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        $this->assertEquals($expected, $this->extension->isApplicable($config));
    }

    /**
     * @return array
     */
    public function isApplicableDataProvider()
    {
        return [
            ['test', null, false],
            [CustomerUserByCustomerExtension::SUPPORTED_GRID, null, false],
            [CustomerUserByCustomerExtension::SUPPORTED_GRID, '', false],
            [CustomerUserByCustomerExtension::SUPPORTED_GRID, 1, true],
        ];
    }

    public function testVisitDatasource()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|DatagridConfiguration $config */
        $config = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->disableOriginalConstructor()
            ->getMock();
        $config->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(CustomerUserByCustomerExtension::SUPPORTED_GRID));
        $this->request->expects($this->any())
            ->method('get')
            ->with(CustomerUserByCustomerExtension::ACCOUNT_KEY)
            ->will($this->returnValue(1));

        $expr = $this->getMockBuilder('Doctrine\ORM\Query\Expr')
            ->disableOriginalConstructor()
            ->getMock();

        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb->expects($this->once())
            ->method('andWhere')
            ->will($this->returnSelf());
        $qb->expects($this->once())
            ->method('setParameter')
            ->with('customer', 1)
            ->will($this->returnSelf());
        $qb->expects($this->once())
            ->method('getRootAliases')
            ->will($this->returnValue(['au']));
        $qb->expects($this->once())
            ->method('expr')
            ->will($this->returnValue($expr));

        /** @var \PHPUnit\Framework\MockObject\MockObject|OrmDatasource $datasource */
        $datasource = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource')
            ->disableOriginalConstructor()
            ->getMock();
        $datasource->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($qb));

        $this->extension->visitDatasource($config, $datasource);
    }
}
