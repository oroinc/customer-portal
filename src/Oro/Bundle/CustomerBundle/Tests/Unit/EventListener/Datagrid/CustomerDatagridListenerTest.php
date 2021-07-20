<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener\Datagrid;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\EventListener\Datagrid\CustomerDatagridListener;
use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;

class CustomerDatagridListenerTest extends \PHPUnit\Framework\TestCase
{
    private const COLUMN_NAME = 'testColumnName';

    /**
     * @var CustomerDatagridListener
     */
    protected $listener;

    /**
     * @var string
     */
    protected $entityClass = 'TestEntity';

    /**
     * @var CustomerUserProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $securityProvider;

    /**
     * @var DatagridInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $datagrid;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->securityProvider = $this->createMock(CustomerUserProvider::class);
        $this->datagrid = $this->createMock(DatagridInterface::class);

        $this->listener = new CustomerDatagridListener($this->securityProvider, [self::COLUMN_NAME]);
    }

    public function testBuildBeforeInvalidDomainObjectException()
    {
        $this->securityProvider->expects($this->any())
            ->method('isGrantedViewCustomerUser')
            ->with($this->entityClass)
            ->willThrowException(new InvalidDomainObjectException('Exception'));

        $this->securityProvider->expects($this->any())->method('getLoggedUser')->willReturn(new CustomerUser());

        $config = $this->getConfig();
        $datagridConfig = DatagridConfiguration::create($config);

        $this->listener->onBuildBefore(new BuildBefore($this->datagrid, $datagridConfig));

        $this->assertEquals($config, $datagridConfig->toArray());
    }

    /**
     * @dataProvider buildBeforeFrontendQuotesProvider
     */
    public function testBuildBefore(array $inputData, array $expectedData)
    {
        $this->securityProvider->expects($this->any())
            ->method('isGrantedViewCustomerUser')
            ->with($this->entityClass)
            ->willReturn($inputData['grantedViewCustomerUser']);

        $this->securityProvider->expects($this->any())->method('getLoggedUser')->willReturn($inputData['user']);

        $datagridConfig = DatagridConfiguration::create($inputData['config']);

        $this->listener->onBuildBefore(new BuildBefore($this->datagrid, $datagridConfig));

        $this->assertEquals($expectedData['config'], $datagridConfig->toArray());
    }

    /**
     * @return array
     */
    public function buildBeforeFrontendQuotesProvider()
    {
        return [
            'invalid user' => [
                'input' => [
                    'user' => null,
                    'config' => $this->getConfig(),
                    'grantedViewCustomerUser' => false,
                ],
                'expected' => [
                    'config' => $this->getConfig(),
                ],
            ],
            'invalid source type' => [
                'input' => [
                    'user' => null,
                    'config' => $this->getConfig(false, true, 'search'),
                    'grantedViewCustomerUser' => false,
                ],
                'expected' => [
                    'config' => $this->getConfig(false, true, 'search'),
                ],
            ],
            'empty [source][query][from]' => [
                'input' => [
                    'user' => new CustomerUser(),
                    'config' => $this->getConfig(false, false),
                    'grantedViewCustomerUser' => true,
                ],
                'expected' => [
                    'config' => $this->getConfig(false, false),
                ],
            ],
            'view not granted' => [
                'input' => [
                    'user' => new CustomerUser(),
                    'config' => $this->getConfig(),
                    'grantedViewCustomerUser' => false,
                ],
                'expected' => [
                    'config' => $this->getConfig(true),
                ],
            ],
            'view granted' => [
                'input' => [
                    'user' => new CustomerUser(),
                    'config' => $this->getConfig(),
                    'grantedViewCustomerUser' => true,
                ],
                'expected' => [
                    'config' => $this->getConfig(),
                ],
            ],
            'acl skipped in datagrid config' => [
                'input' => [
                    'user' => new CustomerUser(),
                    'config' => $this->getConfig(false, false, 'orm', true),
                    'grantedViewCustomerUser' => true,
                ],
                'expected' => [
                    'config' => $this->getConfig(false, false, 'orm', true),
                ],
            ],
        ];
    }

    /**
     * @param bool $empty
     * @param bool $sourceQueryFrom
     * @param string $sourceType
     * @param bool $skipAcl
     * @return array
     */
    protected function getConfig($empty = false, $sourceQueryFrom = true, $sourceType = 'orm', $skipAcl = false)
    {
        $config = [
            'options' => [],
            'source' => [
                'type' => $sourceType,
                'query' => [],
            ],
            'columns' => [],
            'sorters' => [
                'columns' => [],
            ],
            'filters' => [
                'columns' => [],
            ],
            'action_configuration' => null,
        ];

        if ($sourceQueryFrom) {
            $config['source']['query']['from'] = [
                [
                    'table' => $this->entityClass,
                    'alias' => 'tableAlias',
                ],
            ];
        }

        if (!$empty) {
            $config['columns'][self::COLUMN_NAME] = true;
            $config['sorters']['columns'][self::COLUMN_NAME] = true;
            $config['filters']['columns'][self::COLUMN_NAME] = true;
        }

        if ($skipAcl) {
            $config['source']['skip_acl_apply'] = true;
        }

        return $config;
    }
}
