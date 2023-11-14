<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Acl\Cache\CustomerVisitorAclCache;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\CustomerBundle\Form\Handler\AbstractCustomerUserRoleHandler;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Oro\Bundle\SecurityBundle\Cache\DoctrineAclCacheProvider;
use Oro\Bundle\SecurityBundle\Filter\AclPrivilegeConfigurableFilter;
use Oro\Bundle\SecurityBundle\Owner\Metadata\ChainOwnershipMetadataProvider;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Acl\Model\AclCacheInterface;

abstract class AbstractCustomerUserRoleUpdateHandlerTestCase extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FormFactory
     */
    protected $formFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|AclManager
     */
    protected $aclManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|AclPrivilegeRepository
     */
    protected $privilegeRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ChainOwnershipMetadataProvider
     */
    protected $chainMetadataProvider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ConfigProvider
     */
    protected $ownershipConfigProvider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|CustomerUserRoleRepository
     */
    protected $roleRepository;

    /** @var \PHPUnit\Framework\MockObject\MockObject|AclCacheInterface */
    protected $aclCache;

    /** @var \PHPUnit\Framework\MockObject\MockObject|AclPrivilegeConfigurableFilter */
    protected $configurableFilter;

    /** @var \PHPUnit\Framework\MockObject\MockObject|CustomerVisitorAclCache */
    protected $visitorAclCache;

    /** @var \PHPUnit\Framework\MockObject\MockObject|DoctrineAclCacheProvider  */
    protected $queryCacheProvider;

    /**
     * @var array
     */
    protected $privilegeConfig = [
        'entity' => ['types' => ['entity'], 'fix_values' => false, 'show_default' => true],
        'action' => ['types' => ['action'], 'fix_values' => false, 'show_default' => true],
    ];

    /**
     * @var array
     */
    protected $permissionNames = [
        'entity' => ['entity_name'],
        'action' => ['action_name'],
    ];

    protected function setUp(): void
    {
        $this->formFactory = $this->getMockBuilder(FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->aclManager = $this->getMockBuilder(AclManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->privilegeRepository =
            $this->getMockBuilder(AclPrivilegeRepository::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->chainMetadataProvider =
            $this->getMockBuilder(ChainOwnershipMetadataProvider::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->ownershipConfigProvider =
            $this->getMockBuilder(ConfigProvider::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->roleRepository =
            $this->getMockBuilder(CustomerUserRoleRepository::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->visitorAclCache = $this->createMock(CustomerVisitorAclCache::class);
        $this->queryCacheProvider = $this->createMock(DoctrineAclCacheProvider::class);

        $this->doctrineHelper = $this->getMockBuilder(DoctrineHelper::class)
            ->setConstructorArgs([$this->managerRegistry])
            ->getMock();

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityRepository')
            ->willReturn($this->roleRepository);

        $this->aclCache = $this->getMockBuilder(AclCacheInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurableFilter = $this->createMock(AclPrivilegeConfigurableFilter::class);
        $this->configurableFilter->expects($this->any())
            ->method('filter')
            ->willReturnCallback(function ($privileges) {
                return $privileges;
            });
    }

    /**
     * @return AbstractCustomerUserRoleHandler
     */
    abstract protected function getHandler();

    /**
     * @return array
     */
    abstract public function processWithCustomerProvider();

    /**
     * @param CustomerUserRole $role
     * @param Customer|null    $newCustomer
     * @param CustomerUser[]   $appendUsers
     * @param CustomerUser[]   $removedUsers
     * @param CustomerUser[]   $assignedUsers
     * @param CustomerUser[]   $expectedUsersWithRole
     * @param CustomerUser[]   $expectedUsersWithoutRole
     * @param bool             $changeCustomerProcessed
     * @dataProvider processWithCustomerProvider
     */
    public function testProcessWithCustomer(
        CustomerUserRole $role,
        $newCustomer,
        array $appendUsers,
        array $removedUsers,
        array $assignedUsers,
        array $expectedUsersWithRole,
        array $expectedUsersWithoutRole,
        $changeCustomerProcessed = true
    ) {
        $request = new Request();
        $request->setMethod('POST');

        /** @var RequestStack|\PHPUnit\Framework\MockObject\MockObject $requestStack */
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->once())->method('getCurrentRequest')->willReturn($request);

        $this->setUpMocksForProcessWithCustomer(
            $role,
            $appendUsers,
            $removedUsers,
            $assignedUsers,
            $newCustomer,
            $changeCustomerProcessed
        );

        // Array of persisted users
        /** @var CustomerUser[] $persistedUsers */
        $persistedUsers = [];

        $objectManager = $this->createMock(ObjectManager::class);

        $objectManager->expects($this->any())
            ->method('persist')
            ->willReturnCallback(
                function ($entity) use (&$persistedUsers) {
                    if ($entity instanceof CustomerUser) {
                        $persistedUsers[$entity->getEmail()] = $entity;
                    }
                }
            );

        $this->managerRegistry->expects($this->any())
            ->method('getManagerForClass')
            ->with(CustomerUserRole::class)
            ->willReturn($objectManager);

        $handlerInstance = $this->getHandler();

        /** @var \PHPUnit\Framework\MockObject\MockObject|AbstractCustomerUserRoleHandler $handler */
        $handler = $this->getMockBuilder(get_class($handlerInstance))
            ->setMethods(['processPrivileges'])
            ->setConstructorArgs([
                $this->formFactory,
                $this->aclCache,
                $this->privilegeConfig
                ])
            ->getMock();
        $this->setRequirementsForHandler($handler);

        $handler->setRequestStack($requestStack);

        $handler->createForm($role);
        $handler->process($role);

        foreach ($expectedUsersWithRole as $expectedUser) {
            static::assertContainsEquals($expectedUser->getEmail(), $persistedUsers, $expectedUser->getUsername());
            static::assertEquals($persistedUsers[$expectedUser->getEmail()]->getRole($role->getRole()), $role);
        }

        foreach ($expectedUsersWithoutRole as $expectedUser) {
            static::assertContainsEquals($expectedUser->getEmail(), $persistedUsers, $expectedUser->getUsername());
            static::assertEquals($persistedUsers[$expectedUser->getEmail()]->getRole($role->getRole()), null);
        }
    }

    protected function setRequirementsForHandler(AbstractCustomerUserRoleHandler $handler)
    {
        $handler->setAclManager($this->aclManager);
        $handler->setAclPrivilegeRepository($this->privilegeRepository);
        $handler->setChainMetadataProvider($this->chainMetadataProvider);
        $handler->setOwnershipConfigProvider($this->ownershipConfigProvider);
        $handler->setManagerRegistry($this->managerRegistry);
        $handler->setDoctrineHelper($this->doctrineHelper);
        $handler->setConfigurableFilter($this->configurableFilter);
        $handler->setVisitorAclCache($this->visitorAclCache);
        $handler->setQueryCacheProvider($this->queryCacheProvider);
    }

    /**
     * @param CustomerUserRole $role
     * @param int $numberOfUsers
     * @param Customer $customer
     * @param int $offset
     * @return \Oro\Bundle\CustomerBundle\Entity\CustomerUser[]
     */
    protected function createUsersWithRole(
        CustomerUserRole $role,
        $numberOfUsers,
        Customer $customer = null,
        $offset = 0
    ) {
        /** @var CustomerUser[] $users */
        $users = [];
        for ($i = 0; $i < $numberOfUsers; $i++) {
            $userId = $offset + $i + 1;
            $user = new CustomerUser();
            $user->setUsername('user_id_' . $userId . '_role_' . $role->getRole());
            $user->setRoles([$role]);
            $user->setCustomer($customer);
            $users[$userId] = $user;
        }

        return $users;
    }

    /**
     * @param string   $role
     * @param int|null $id
     * @return CustomerUserRole
     */
    protected function createCustomerUserRole($role, $id = null)
    {
        $entity = new CustomerUserRole($role);
        if ($id) {
            $reflection = new \ReflectionProperty(CustomerUserRole::class, 'id');
            $reflection->setAccessible(true);
            $reflection->setValue($entity, $id);
        }

        return $entity;
    }

    /**
     * @param CustomerUserRole $role
     * @param array            $appendUsers
     * @param array            $removedUsers
     * @param array            $assignedUsers
     * @param Customer|null    $newCustomer
     * @param bool             $changeCustomerProcessed
     */
    protected function setUpMocksForProcessWithCustomer(
        CustomerUserRole $role,
        array $appendUsers,
        array $removedUsers,
        array $assignedUsers,
        $newCustomer,
        $changeCustomerProcessed
    ) {
        $appendForm = $this->createMock(FormInterface::class);
        $appendForm->expects($this->once())
            ->method('getData')
            ->willReturn($appendUsers);

        $removeForm = $this->createMock(FormInterface::class);
        $removeForm->expects($this->once())
            ->method('getData')
            ->willReturn($removedUsers);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('submit')
            ->willReturnCallback(
                function () use ($role, $newCustomer) {
                    $role->setCustomer($newCustomer);
                    $role->setOrganization($newCustomer->getOrganization());
                }
            );
        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $form->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    ['appendUsers', $appendForm],
                    ['removeUsers', $removeForm],
                ]
            );

        $this->formFactory->expects($this->once())
            ->method('create')
            ->willReturn($form);

        $this->roleRepository->expects($changeCustomerProcessed ? $this->once() : $this->never())
            ->method('getAssignedUsers')
            ->with($role)
            ->willReturn($assignedUsers);
    }

    protected function prepareUsersAndRoles()
    {
        $oldCustomer = $this->getEntity(Customer::class, ['id' => 1]);
        $newCustomer1 = $this->getEntity(Customer::class, ['id' => 10]);
        $role1 = $this->createCustomerUserRole('test role1', 1);
        $users1 =
            $this->createUsersWithRole($role1, 6, $newCustomer1)
            + $this->createUsersWithRole($role1, 2, $oldCustomer, 6);

        $newCustomer2 = $this->getEntity(Customer::class, ['id' => 20]);
        $oldAcc2 = $this->getEntity(Customer::class, ['id' => 21]);
        $role2 = $this->createCustomerUserRole('test role2', 2);
        $role2->setCustomer($oldAcc2);
        $users2 =
            $this->createUsersWithRole($role2, 6, $newCustomer2)
            + $this->createUsersWithRole($role2, 2, $oldAcc2, 6);

        $role3 = $this->createCustomerUserRole('test role3', 3);
        $role3->setCustomer($this->getEntity(Customer::class, ['id' => 31]));
        $users3 = $this->createUsersWithRole($role3, 6, $role3->getCustomer());

        $newCustomer4 = $this->getEntity(Customer::class, ['id' => 41]);
        $role4 = $this->createCustomerUserRole('test role4', 4);
        $role4->setCustomer($this->getEntity(Customer::class, ['id' => 40]));
        $users4 = $this->createUsersWithRole($role4, 6, $newCustomer4);

        $newCustomer5 = $this->getEntity(Customer::class, ['id' => 50]);
        $role5 = $this->createCustomerUserRole('test role5');
        $role5->setCustomer($this->getEntity(Customer::class, ['id' => 51]));
        $users5 = $this->createUsersWithRole($role5, 6, $newCustomer5);

        return [
            [
                $users1,
                $users2,
                $users3,
                $users4,
                $users5,
            ],
            [
                $role1,
                $role2,
                $role3,
                $role4,
                $role5,
            ],
            [
                $newCustomer1,
                $newCustomer2,
                $newCustomer4,
                $newCustomer5,
            ],
        ];
    }
}
