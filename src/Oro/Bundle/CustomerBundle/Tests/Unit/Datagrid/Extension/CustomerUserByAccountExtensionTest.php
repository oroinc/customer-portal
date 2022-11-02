<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid\Extension;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
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

    protected function setUp(): void
    {
        $this->request = $this->createMock(Request::class);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->any())
            ->method('getCurrentRequest')
            ->willReturn($this->request);

        $this->extension = new CustomerUserByCustomerExtension();
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
            ->willReturn($customerId);

        $config = $this->createMock(DatagridConfiguration::class);
        $config->expects($this->any())
            ->method('getName')
            ->willReturn($name);

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
        $config = $this->createMock(DatagridConfiguration::class);
        $config->expects($this->any())
            ->method('getName')
            ->willReturn(CustomerUserByCustomerExtension::SUPPORTED_GRID);
        $this->request->expects($this->any())
            ->method('get')
            ->with(CustomerUserByCustomerExtension::ACCOUNT_KEY)
            ->willReturn(1);

        $expr = $this->createMock(Expr::class);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->expects($this->once())
            ->method('andWhere')
            ->willReturnSelf();
        $qb->expects($this->once())
            ->method('setParameter')
            ->with('customer', 1)
            ->willReturnSelf();
        $qb->expects($this->once())
            ->method('getRootAliases')
            ->willReturn(['au']);
        $qb->expects($this->once())
            ->method('expr')
            ->willReturn($expr);

        $datasource = $this->createMock(OrmDatasource::class);
        $datasource->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($qb);

        $this->extension->visitDatasource($config, $datasource);
    }
}
