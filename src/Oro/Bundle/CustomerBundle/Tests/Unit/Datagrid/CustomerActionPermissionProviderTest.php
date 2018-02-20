<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\CustomerBundle\Datagrid\CustomerActionPermissionProvider;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CustomerActionPermissionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerActionPermissionProvider
     */
    protected $actionPermissionProvider;

    /**
     * @var ResultRecordInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $record;

    /**
     * @var AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authorizationChecker;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrine;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->record = $this->createMock('Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface');

        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->actionPermissionProvider = new CustomerActionPermissionProvider(
            $this->authorizationChecker,
            $this->doctrine
        );
    }

    /**
     * @param array $inputData
     * @param array $expectedData
     *
     * @dataProvider getActionsProvider
     */
    public function testGetActions(array $inputData, array $expectedData)
    {
        $this->record->expects($this->any())
            ->method('getValue')
            ->with('id')
            ->willReturn($inputData['record']['id']);

        $this->doctrine->expects($this->any())
            ->method('getManagerForClass')
            ->with($expectedData['class'])
            ->willReturn($this->manager)
        ;

        $this->manager->expects($this->any())
            ->method('getReference')
            ->with($expectedData['class'], $expectedData['id'])
            ->willReturn($inputData['object'])
        ;

        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->willReturnCallback(function ($permission) use ($inputData) {
                return $inputData['isGranted'][$permission];
            })
        ;

        $this->assertEquals(
            $expectedData['actions'],
            $this->actionPermissionProvider->getActions($this->record, $inputData['config'])
        );
    }

    /**
     * @return array
     */
    public function getActionsProvider()
    {
        return [
            'empty config' => [
                'input'     => [
                    'object' => null,
                    'record' => [
                        'id' => 1,
                    ],
                    'config' => [
                        'action1' => [],
                        'action2' => [],
                        'action3' => [],
                    ],
                ],
                'expected'  => [
                    'id' => 1,
                    'class' => null,
                    'actions' => [
                        'action1' => true,
                        'action2' => true,
                        'action3' => true,
                    ],
                ],
            ],
            '!Action1' => [
                'input'     => [
                    'isGranted' => [
                        'PERMISSION2' => false,
                    ],
                    'object' => new \stdClass(),
                    'record' => [
                        'id' => 2,
                    ],
                    'config' => [
                        'action1' => [],
                        'action2' => [
                            'acl_permission' => 'PERMISSION2',
                            'acl_class' => 'TestClass',
                        ],
                        'action3' => [],
                    ],
                ],
                'expected'  => [
                    'id' => 2,
                    'class' => 'TestClass',
                    'object' => new \stdClass(),
                    'actions' => [
                        'action1' => true,
                        'action2' => false,
                        'action3' => true,
                    ],
                ],
            ],
        ];
    }
}
