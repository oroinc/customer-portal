<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\CustomerBundle\Datagrid\ActionPermissionProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

class ActionPermissionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ActionPermissionProvider
     */
    protected $actionPermissionProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResultRecordInterface
     */
    protected $record;

    /**
     * @var array
     */
    protected $actionsList = [
        'enable',
        'disable',
        'view',
        'update',
        'delete'
    ];

    /**
     * @var array
     */
    protected $customerUserRoleActionList = [
        'view',
        'update'
    ];

    /** @var \PHPUnit_Framework_MockObject_MockObject|AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var \PHPUnit_Framework_MockObject_MockObject|TokenAccessorInterface */
    protected $tokenAccessor;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->record = $this->createMock(ResultRecordInterface::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->actionPermissionProvider = new ActionPermissionProvider(
            $this->authorizationChecker,
            $this->tokenAccessor
        );
    }

    /**
     * @return array
     */
    public function recordConditions()
    {
        return [
            'enabled record' => [
                'isRecordEnabled' => true,
                'expected' => [
                    'enable' => false,
                    'disable' => true,
                    'view' => true,
                    'update' => true,
                    'delete' => true
                ],
                'user' => new CustomerUser()
            ],
            'disabled record' => [
                'isRecordEnabled' => false,
                'expected' => [
                    'enable' => true,
                    'disable' => false,
                    'view' => true,
                    'update' => true,
                    'delete' => true
                ],
                'user' => new User()
            ]
        ];
    }

    /**
     * @param boolean  $isRolePredefined
     * @param boolean  $isGranted
     * @param array    $expected
     *
     * @dataProvider getCustomerUserRolePermissionProvider
     */
    public function testGetCustomerUserRolePermission($isRolePredefined, $isGranted, array $expected)
    {
        $this->record->expects($this->any())
            ->method('getValue')
            ->with($this->isType('string'))
            ->willReturn($isRolePredefined);

        $this->authorizationChecker->expects($isRolePredefined ? $this->once() : $this->never())
            ->method('isGranted')
            ->with($this->isType('string'))
            ->willReturn($isGranted);

        $result = $this->actionPermissionProvider->getCustomerUserRolePermission($this->record);

        $this->assertCount(count($this->customerUserRoleActionList), $result);

        foreach ($this->customerUserRoleActionList as $action) {
            $this->assertArrayHasKey($action, $result);
        }

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getCustomerUserRolePermissionProvider()
    {
        return [
            'user have permission to create and role is predefined' => [
                'isRolePredefined' => true,
                'isGranted' => true,
                'expected' => [
                    'view' => true,
                    'update' => true
                ]
            ],
            'user have no permission to create and role is predefined' => [
                'isRolePredefined' => true,
                'isGranted' => false,
                'expected' => [
                    'view' => true,
                    'update' => false
                ]
            ],
            'user have no permission to create and role is no predefined' => [
                'isRolePredefined' => false,
                'isGranted' => false,
                'expected' => [
                    'view' => true,
                    'update' => true
                ]
            ],
        ];
    }
}
