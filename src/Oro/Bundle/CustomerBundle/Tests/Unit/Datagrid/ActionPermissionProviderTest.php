<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid;

use Oro\Bundle\CustomerBundle\Datagrid\ActionPermissionProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ActionPermissionProviderTest extends \PHPUnit\Framework\TestCase
{
    private array $customerUserRoleActionList = ['view', 'update'];

    /** @var \PHPUnit\Framework\MockObject\MockObject|ResultRecordInterface */
    private $record;

    /** @var \PHPUnit\Framework\MockObject\MockObject|AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var \PHPUnit\Framework\MockObject\MockObject|TokenAccessorInterface */
    private $tokenAccessor;

    /** @var ActionPermissionProvider */
    private $actionPermissionProvider;

    protected function setUp(): void
    {
        $this->record = $this->createMock(ResultRecordInterface::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->actionPermissionProvider = new ActionPermissionProvider(
            $this->authorizationChecker,
            $this->tokenAccessor
        );
    }

    public function recordConditions(): array
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
     * @dataProvider getCustomerUserRolePermissionProvider
     */
    public function testGetCustomerUserRolePermission(bool $isRolePredefined, bool $isGranted, array $expected)
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

    public function getCustomerUserRolePermissionProvider(): array
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
