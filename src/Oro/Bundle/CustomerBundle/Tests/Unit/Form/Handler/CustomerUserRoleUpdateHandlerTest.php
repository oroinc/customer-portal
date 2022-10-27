<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Handler\AbstractCustomerUserRoleHandler;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserRoleUpdateHandler;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleType;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadataProvider;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class CustomerUserRoleUpdateHandlerTest extends AbstractCustomerUserRoleUpdateHandlerTestCase
{
    /** @var CustomerUserRoleUpdateHandler */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = $this->getHandler();
        $this->setRequirementsForHandler($this->handler);
    }

    protected function getHandler(): AbstractCustomerUserRoleHandler
    {
        if (!$this->handler) {
            $this->handler = new CustomerUserRoleUpdateHandler(
                $this->formFactory,
                $this->aclCache,
                $this->privilegeConfig
            );
        }

        return $this->handler;
    }

    public function testCreateForm(): void
    {
        $role = new CustomerUserRole('TEST');

        $expectedConfig = $this->privilegeConfig;
        foreach ($expectedConfig as $key => $value) {
            $expectedConfig[$key]['permissions'] = $this->getPermissionNames($value['types']);
        }

        $this->privilegeRepository->expects(self::any())
            ->method('getPermissionNames')
            ->with($this->isType('array'))
            ->willReturnCallback(function ($types) {
                return $this->getPermissionNames($types);
            });

        $expectedForm = $this->createMock(FormInterface::class);

        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(CustomerUserRoleType::class, $role, ['privilege_config' => $expectedConfig])
            ->willReturn($expectedForm);

        $actualForm = $this->handler->createForm($role);
        self::assertEquals($expectedForm, $actualForm);
    }

    private function getPermissionNames(array $types): array
    {
        $names = [];
        foreach ($types as $type) {
            if (isset($this->permissionNames[$type])) {
                $names[] = $this->permissionNames[$type];
            }
        }

        return array_merge(...$names);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSetRolePrivileges(): void
    {
        $role = new CustomerUserRole('TEST');
        $roleSecurityIdentity = new RoleSecurityIdentity($role);

        $firstClass = 'FirstClass';
        $secondClass = 'SecondClass';
        $unknownClass = 'UnknownClass';

        $request = new Request();
        $request->setMethod('GET');

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $firstEntityPrivilege = $this->createPrivilege('entity', 'entity:' . $firstClass, 'VIEW', true);
        $firstEntityConfig = $this->getClassConfig(true);

        $secondEntityPrivilege = $this->createPrivilege('entity', 'entity:' . $secondClass, 'VIEW', true);
        $secondEntityConfig = $this->getClassConfig(false);

        $unknownEntityPrivilege = $this->createPrivilege('entity', 'entity:' . $unknownClass, 'VIEW', true);

        $actionPrivilege = $this->createPrivilege('action', 'action', 'random_action', true);

        $privilegesForm = $this->createMock(FormInterface::class);
        $privilegesForm->expects(self::once())
            ->method('setData');

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::any())
            ->method('get')
            ->willReturnMap([
                ['privileges', $privilegesForm],
            ]);

        $this->formFactory->expects(self::once())
            ->method('create')
            ->willReturn($form);

        $this->chainMetadataProvider->expects(self::once())
            ->method('startProviderEmulation')
            ->with(FrontendOwnershipMetadataProvider::ALIAS);
        $this->chainMetadataProvider->expects(self::once())
            ->method('stopProviderEmulation');

        $this->aclManager->expects(self::any())
            ->method('getSid')
            ->with($role)
            ->willReturn($roleSecurityIdentity);

        $this->privilegeRepository->expects(self::any())
            ->method('getPrivileges')
            ->with($roleSecurityIdentity)
            ->willReturn(
                new ArrayCollection(
                    [$firstEntityPrivilege, $secondEntityPrivilege, $unknownEntityPrivilege, $actionPrivilege]
                )
            );

        $this->ownershipConfigProvider->expects(self::any())
            ->method('hasConfig')
            ->willReturnMap([
                [$firstClass, null, true],
                [$secondClass, null, true],
                [$unknownClass, null, false],
            ]);
        $this->ownershipConfigProvider->expects(self::any())
            ->method('getConfig')
            ->willReturnMap([
                [$firstClass, null, $firstEntityConfig],
                [$secondClass, null, $secondEntityConfig],
            ]);

        $this->handler->setRequestStack($requestStack);
        $this->handler->createForm($role);
        $this->handler->process($role);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testProcessPrivileges(): void
    {
        $request = new Request();
        $request->setMethod('POST');

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $role = new CustomerUserRole('TEST');
        $roleSecurityIdentity = new RoleSecurityIdentity($role);

        $objectIdentity = new ObjectIdentity('entity', 'EntityClass');

        $appendForm = $this->createMock(FormInterface::class);
        $appendForm->expects(self::once())
            ->method('getData')
            ->willReturn([]);

        $removeForm = $this->createMock(FormInterface::class);
        $removeForm->expects(self::once())
            ->method('getData')
            ->willReturn([]);

        $entityForm = $this->createMock(FormInterface::class);
        $actionForm = $this->createMock(FormInterface::class);

        $privilegesData = json_encode([
            'entity' => [
                0 => [
                    'identity' => [
                        'id' => 'entity:FirstClass',
                        'name' => 'VIEW',
                    ],
                    'permissions' => [
                        'VIEW' => [
                            'accessLevel' => 5,
                            'name' => 'VIEW',
                        ],
                    ],
                ],
                1 => [
                    'identity' => [
                        'id' => 'entity:SecondClass',
                        'name' => 'VIEW',
                    ],
                    'permissions' => [
                        'VIEW' => [
                            'accessLevel' => 5,
                            'name' => 'VIEW',
                        ],
                    ],
                ]
            ],
            'action' => [
                0 => [
                    'identity' => [
                        'id' => 'action',
                        'name' => 'random_action',
                    ],
                    'permissions' => [
                        'random_action' => [
                            'accessLevel' => 5,
                            'name' => 'random_action',
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);
        $privilegesForm = $this->createMock(FormInterface::class);
        $privilegesForm->expects(self::once())
            ->method('getData')
            ->willReturn($privilegesData);

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::any())
            ->method('getName')
            ->willReturn('formName');
        $form->expects(self::once())
            ->method('submit');
        $form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);
        $form->expects(self::any())
            ->method('get')
            ->willReturnMap([
                ['appendUsers', $appendForm],
                ['removeUsers', $removeForm],
                ['entity', $entityForm],
                ['action', $actionForm],
                ['privileges', $privilegesForm],
            ]);

        $this->formFactory->expects(self::once())
            ->method('create')
            ->willReturn($form);

        $objectManager = $this->createMock(EntityManagerInterface::class);
        $this->managerRegistry->expects(self::any())
            ->method('getManagerForClass')
            ->with(get_class($role))
            ->willReturn($objectManager);

        $configuration = $this->createMock(Configuration::class);
        $cache = $this->createMock(AbstractAdapter::class);
        $this->managerRegistry->expects(self::once())
            ->method('getManager')
            ->willReturn($objectManager);
        $objectManager->expects(self::once())
            ->method('getConfiguration')
            ->willReturn($configuration);
        $configuration->expects(self::once())
            ->method('getQueryCache')
            ->willReturn($cache);
        $cache->expects(self::once())
            ->method('clear');

        $expectedFirstEntityPrivilege = $this->createPrivilege('entity', 'entity:FirstClass', 'VIEW');
        $expectedFirstEntityPrivilege->setGroup(CustomerUser::SECURITY_GROUP);

        $expectedSecondEntityPrivilege = $this->createPrivilege('entity', 'entity:SecondClass', 'VIEW');
        $expectedSecondEntityPrivilege->setGroup(CustomerUser::SECURITY_GROUP);

        $expectedActionPrivilege = $this->createPrivilege('action', 'action', 'random_action');
        $expectedActionPrivilege->setGroup(CustomerUser::SECURITY_GROUP);

        $this->privilegeRepository->expects(self::once())
            ->method('savePrivileges')
            ->with(
                $roleSecurityIdentity,
                new ArrayCollection(
                    [$expectedFirstEntityPrivilege, $expectedSecondEntityPrivilege, $expectedActionPrivilege]
                )
            );

        $this->aclManager->expects(self::any())
            ->method('getSid')
            ->with($role)
            ->willReturn($roleSecurityIdentity);

        $this->aclManager->expects(self::any())
            ->method('getOid')
            ->with($objectIdentity->getIdentifier() . ':' . $objectIdentity->getType())
            ->willReturn($objectIdentity);

        $this->chainMetadataProvider->expects(self::once())
            ->method('startProviderEmulation')
            ->with(FrontendOwnershipMetadataProvider::ALIAS);
        $this->chainMetadataProvider->expects(self::once())
            ->method('stopProviderEmulation');

        $handler = new CustomerUserRoleUpdateHandler($this->formFactory, $this->aclCache, $this->privilegeConfig);

        $this->setRequirementsForHandler($handler);
        $handler->setRequestStack($requestStack);

        $handler->createForm($role);
        $handler->process($role);
    }

    public function processWithCustomerProvider(): array
    {
        /** @var CustomerUser[] $users */
        /** @var CustomerUserRole[] $roles */
        /** @var Customer[] $customers */
        [$users, $roles, $customers] = $this->prepareUsersAndRoles();

        [$users1, $users2, $users3, $users4, $users5] = $users;
        [$role1, $role2, $role3, $role4, $role5] = $roles;
        [$newCustomer1, $newCustomer2, $newCustomer4, $newCustomer5] = $customers;

        return [
            'set customer for role without customer (assigned users should be removed except appendUsers)' => [
                'role'                     => $role1,
                'newCustomer'              => $newCustomer1,
                'appendUsers'              => [$users1[1], $users1[5], $users1[6]],
                'removedUsers'             => [$users1[3], $users1[4]],
                'assignedUsers'            => [$users1[1], $users1[2], $users1[3], $users1[4], $users1[7]],
                'expectedUsersWithRole'    => [$users1[5], $users1[6]], // $users[1] already has role
                'expectedUsersWithoutRole' => [$users1[7], $users1[3], $users1[4]],
            ],
            'set another customer for role with customer (assigned users should be removed except appendUsers)' => [
                'role'                     => $role2,
                'newCustomer'              => $newCustomer2,
                'appendUsers'              => [$users2[1], $users2[5], $users2[6]],
                'removedUsers'             => [$users2[3], $users2[4]],
                'assignedUsers'            => [$users2[1], $users2[2], $users2[3], $users2[4], $users1[7], $users1[8]],
                'expectedUsersWithRole'    => [$users2[5], $users2[6]], // $users0 not changed, because already has role
                'expectedUsersWithoutRole' => [$users1[7], $users1[8], $users2[3], $users2[4]],
            ],
            'add/remove users for role with customer (customer not changed)' => [
                'role'                     => $role3,
                'newCustomer'              => $role3->getCustomer(),
                'appendUsers'              => [$users3[5], $users3[6]],
                'removedUsers'             => [$users3[3], $users3[4]],
                'assignedUsers'            => [$users3[1], $users3[2], $users3[3], $users3[4]],
                'expectedUsersWithRole'    => [$users3[5], $users3[6]],
                'expectedUsersWithoutRole' => [$users3[3], $users3[4]],
                'changeCustomerProcessed'  => false,
            ],
            'remove customer for role with customer (assigned users should not be removed)' => [
                'role'                     => $role4,
                'newCustomer'              => $newCustomer4,
                'appendUsers'              => [$users4[1], $users4[5], $users4[6]],
                'removedUsers'             => [$users4[3], $users4[4]],
                'assignedUsers'            => [$users4[1], $users4[2], $users4[3], $users4[4]],
                'expectedUsersWithRole'    => [$users4[5], $users4[6]],
                'expectedUsersWithoutRole' => [$users4[3], $users4[4]],
            ],
            'change customer logic shouldn\'t be processed (role without ID)' => [
                'role'                     => $role5,
                'newCustomer'              => $newCustomer5,
                'appendUsers'              => [$users5[1], $users5[5], $users5[6]],
                'removedUsers'             => [$users5[3], $users5[4]],
                'assignedUsers'            => [$users5[1], $users5[2], $users5[3], $users5[4]],
                'expectedUsersWithRole'    => [$users5[5], $users5[6]],
                'expectedUsersWithoutRole' => [$users5[3], $users5[4]],
                'changeCustomerProcessed'  => false,
            ],
        ];
    }

    private function createPrivilege(
        string $extensionKey,
        string $id,
        string $name,
        bool $setExtensionKey = false
    ): AclPrivilege {
        $privilege = new AclPrivilege();
        if ($setExtensionKey) {
            $privilege->setExtensionKey($extensionKey);
        }
        $privilege->setIdentity(new AclPrivilegeIdentity($id, $name));
        $privilege->addPermission(new AclPermission($name, 5));

        return $privilege;
    }

    private function getClassConfig(bool $hasFrontendOwner): ConfigInterface
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->expects(self::any())
            ->method('has')
            ->with('frontend_owner_type')
            ->willReturn($hasFrontendOwner);

        return $config;
    }

    public function testGetCustomerUserRolePrivilegeConfig(): void
    {
        $role = new CustomerUserRole('');
        self::assertIsArray($this->handler->getCustomerUserRolePrivilegeConfig($role));
        self::assertEquals($this->privilegeConfig, $this->handler->getCustomerUserRolePrivilegeConfig($role));
    }

    /**
     * @dataProvider CustomerUserRolePrivilegesDataProvider
     */
    public function testGetCustomerUserRolePrivileges(ArrayCollection $privileges, array $expected): void
    {
        $privilegeConfig = [
            'entity' => ['types' => ['entity'], 'fix_values' => false, 'show_default' => true],
            'action' => ['types' => ['action'], 'fix_values' => false, 'show_default' => true],
            'default' => ['types' => ['(default)'], 'fix_values' => true, 'show_default' => false],
        ];
        $handler = new CustomerUserRoleUpdateHandler($this->formFactory, $this->aclCache, $privilegeConfig);
        $this->setRequirementsForHandler($handler);

        $role = new CustomerUserRole('ROLE_ADMIN');
        $securityIdentity = new RoleSecurityIdentity($role);
        $this->aclManager->expects(self::once())
            ->method('getSid')
            ->with($role)
            ->willReturn($securityIdentity);
        $this->privilegeRepository->expects(self::once())
            ->method('getPrivileges')
            ->with($securityIdentity)
            ->willReturn($privileges);
        $this->chainMetadataProvider->expects(self::once())
            ->method('startProviderEmulation')
            ->with(FrontendOwnershipMetadataProvider::ALIAS);
        $this->chainMetadataProvider->expects(self::once())
            ->method('stopProviderEmulation');
        $result = $handler->getCustomerUserRolePrivileges($role);

        self::assertEquals(array_keys($expected), array_keys($result));
        /**
         * @var string $key
         * @var ArrayCollection $value
         */
        foreach ($expected as $key => $value) {
            self::assertEquals($value->getValues(), $result[$key]->getValues());
        }
    }

    public function customerUserRolePrivilegesDataProvider(): array
    {
        $privilegesForEntity = [
            ['VIEW', 2],
            ['CREATE', 2],
            ['EDIT', 2],
            ['DELETE', 2],
            ['SHARE', 2],
        ];
        $privilegesForEntity2 = [
            ['VIEW', 222],
            ['CREATE', 2],
            ['EDIT', 2],
            ['DELETE', 2],
            ['SHARE', 2],
        ];
        $privilegesForAction = [
            ['EXECUTE', 5],
        ];
        return [
            'get and sorted privileges' => [
                'privileges' => $this->createPrivileges(
                    [
                        [
                            'total' => 10,
                            'extensionKey' => 'entity',
                            'identityName' => null,
                            'aclPermissions' => $privilegesForEntity,
                        ],
                        [
                            'total' => 5,
                            'extensionKey' => 'action',
                            'identityName' => null,
                            'aclPermissions' => $privilegesForAction,
                        ],
                        [
                            'total' => 3,
                            'extensionKey' => 'testExtension',
                            'identityName' => null,
                            'aclPermissions' => $privilegesForEntity,
                        ],
                        [
                            'total' => 2,
                            'extensionKey' => '(default)',
                            'identityName' => '(default)',
                            'aclPermissions' => $privilegesForEntity,
                        ],
                        [
                            'total' => 1,
                            'extensionKey' => '(default)',
                            'identityName' => null,
                            'aclPermissions' => $privilegesForEntity,
                        ],
                    ]
                ),
                'expected' => [
                    'entity' => $this->createPrivileges([
                        [
                            'total' => 10,
                            'extensionKey' => 'entity',
                            'identityName' => null,
                            'aclPermissions' => $privilegesForEntity,
                        ],
                    ]),
                    'action' => $this->createPrivileges([
                        [
                            'total' => 5,
                            'extensionKey' => 'action',
                            'identityName' => null,
                            'aclPermissions' => $privilegesForAction,
                        ],
                    ]),
                    'default' => $this->createPrivileges([
                        [
                            'total' => 1,
                            'extensionKey' => '(default)',
                            'identityName' => null,
                            'aclPermissions' => $privilegesForEntity2,
                        ],
                    ]),
                ],
            ],
        ];
    }

    private function createPrivileges(array $config): ArrayCollection
    {
        $privileges = new ArrayCollection();
        foreach ($config as $value) {
            for ($i = 1; $i <= $value['total']; $i++) {
                $privilege = new AclPrivilege();
                $privilege->setExtensionKey($value['extensionKey']);
                $identityName = $value['identityName'] ?: 'EntityClass_' . $i;
                $privilege->setIdentity(new AclPrivilegeIdentity($i, $identityName));
                $privilege->setGroup('commerce');
                foreach ($value['aclPermissions'] as $aclPermission) {
                    [$name, $accessLevel] = $aclPermission;
                    $privilege->addPermission(new AclPermission($name, $accessLevel));
                }
                $privileges->add($privilege);
            }
        }
        return $privileges;
    }
}
