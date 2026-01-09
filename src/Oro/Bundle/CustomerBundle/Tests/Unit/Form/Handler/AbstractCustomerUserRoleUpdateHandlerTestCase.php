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
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Acl\Model\AclCacheInterface;

abstract class AbstractCustomerUserRoleUpdateHandlerTestCase extends TestCase
{
    protected FormFactory&MockObject $formFactory;
    protected AclManager&MockObject $aclManager;
    protected AclPrivilegeRepository&MockObject $privilegeRepository;
    protected ChainOwnershipMetadataProvider&MockObject $chainMetadataProvider;
    protected ConfigProvider&MockObject $ownershipConfigProvider;
    protected ManagerRegistry&MockObject $managerRegistry;
    protected DoctrineHelper&MockObject $doctrineHelper;
    protected CustomerUserRoleRepository&MockObject $roleRepository;
    protected AclCacheInterface&MockObject $aclCache;
    protected AclPrivilegeConfigurableFilter&MockObject $configurableFilter;
    protected CustomerVisitorAclCache&MockObject $visitorAclCache;
    protected DoctrineAclCacheProvider&MockObject $queryCacheProvider;

    protected array $privilegeConfig = [
        'entity' => ['types' => ['entity'], 'fix_values' => false, 'show_default' => true],
        'action' => ['types' => ['action'], 'fix_values' => false, 'show_default' => true],
    ];

    protected array $permissionNames = [
        'entity' => ['entity_name'],
        'action' => ['action_name'],
    ];

    #[\Override]
    protected function setUp(): void
    {
        $this->formFactory = $this->createMock(FormFactory::class);
        $this->aclManager = $this->createMock(AclManager::class);
        $this->privilegeRepository = $this->createMock(AclPrivilegeRepository::class);
        $this->chainMetadataProvider = $this->createMock(ChainOwnershipMetadataProvider::class);
        $this->ownershipConfigProvider = $this->createMock(ConfigProvider::class);
        $this->roleRepository = $this->createMock(CustomerUserRoleRepository::class);
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->visitorAclCache = $this->createMock(CustomerVisitorAclCache::class);
        $this->queryCacheProvider = $this->createMock(DoctrineAclCacheProvider::class);

        $this->doctrineHelper = $this->getMockBuilder(DoctrineHelper::class)
            ->setConstructorArgs([$this->managerRegistry])
            ->getMock();

        $this->doctrineHelper->expects(self::any())
            ->method('getEntityRepository')
            ->willReturn($this->roleRepository);

        $this->aclCache = $this->createMock(AclCacheInterface::class);

        $this->configurableFilter = $this->createMock(AclPrivilegeConfigurableFilter::class);
        $this->configurableFilter->expects(self::any())
            ->method('filter')
            ->willReturnCallback(function ($privileges) {
                return $privileges;
            });
    }

    abstract protected function getHandler(): AbstractCustomerUserRoleHandler;

    abstract public function processWithCustomerProvider(): array;

    /**
     * @param CustomerUserRole $role
     * @param Customer         $newCustomer
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
        Customer $newCustomer,
        array $appendUsers,
        array $removedUsers,
        array $assignedUsers,
        array $expectedUsersWithRole,
        array $expectedUsersWithoutRole,
        bool $changeCustomerProcessed = true
    ): void {
        $request = new Request();
        $request->setMethod('POST');

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects(self::exactly(2))
            ->method('getCurrentRequest')
            ->willReturn($request);

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

        $objectManager->expects(self::any())
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$persistedUsers) {
                if ($entity instanceof CustomerUser) {
                    $persistedUsers[$entity->getEmail()] = $entity;
                }
            });

        $this->managerRegistry->expects(self::any())
            ->method('getManagerForClass')
            ->with(CustomerUserRole::class)
            ->willReturn($objectManager);

        $handlerInstance = $this->getHandler();

        /** @var AbstractCustomerUserRoleHandler $handler */
        $handler = $this->getMockBuilder(get_class($handlerInstance))
            ->onlyMethods(['processPrivileges'])
            ->setConstructorArgs([
                $this->formFactory,
                $this->aclCache,
                $this->queryCacheProvider,
                $this->privilegeConfig
                ])
            ->getMock();
        $this->setRequirementsForHandler($handler);

        $handler->setRequestStack($requestStack);

        $handler->createForm($role);
        $handler->process($role);

        foreach ($expectedUsersWithRole as $expectedUser) {
            self::assertContainsEquals($expectedUser->getEmail(), $persistedUsers, $expectedUser->getUserIdentifier());
            self::assertEquals($persistedUsers[$expectedUser->getEmail()]->getUserRole($role->getRole()), $role);
        }

        foreach ($expectedUsersWithoutRole as $expectedUser) {
            self::assertContainsEquals($expectedUser->getEmail(), $persistedUsers, $expectedUser->getUserIdentifier());
            self::assertEquals($persistedUsers[$expectedUser->getEmail()]->getUserRole($role->getRole()), null);
        }
    }

    protected function setRequirementsForHandler(AbstractCustomerUserRoleHandler $handler): void
    {
        $handler->setAclManager($this->aclManager);
        $handler->setAclPrivilegeRepository($this->privilegeRepository);
        $handler->setChainMetadataProvider($this->chainMetadataProvider);
        $handler->setOwnershipConfigProvider($this->ownershipConfigProvider);
        $handler->setManagerRegistry($this->managerRegistry);
        $handler->setDoctrineHelper($this->doctrineHelper);
        $handler->setConfigurableFilter($this->configurableFilter);
        $handler->setVisitorAclCache($this->visitorAclCache);
    }

    protected function createCustomer(int $id): Customer
    {
        $customer = new Customer();
        ReflectionUtil::setId($customer, $id);

        return $customer;
    }

    /**
     * @return CustomerUser[]
     */
    protected function createUsersWithRole(
        CustomerUserRole $role,
        int $numberOfUsers,
        ?Customer $customer = null,
        int $offset = 0
    ): array {
        /** @var CustomerUser[] $users */
        $users = [];
        for ($i = 0; $i < $numberOfUsers; $i++) {
            $userId = $offset + $i + 1;
            $user = new CustomerUser();
            $user->setUsername('user_id_' . $userId . '_role_' . $role->getRole());
            $user->setUserRoles([$role]);
            $user->setCustomer($customer);
            $users[$userId] = $user;
        }

        return $users;
    }

    protected function createCustomerUserRole(string $role, ?int $id = null): CustomerUserRole
    {
        $entity = new CustomerUserRole($role);
        ReflectionUtil::setId($entity, $id);

        return $entity;
    }

    protected function setUpMocksForProcessWithCustomer(
        CustomerUserRole $role,
        array $appendUsers,
        array $removedUsers,
        array $assignedUsers,
        ?Customer $newCustomer,
        bool $changeCustomerProcessed
    ): void {
        $appendForm = $this->createMock(FormInterface::class);
        $appendForm->expects(self::once())
            ->method('getData')
            ->willReturn($appendUsers);

        $removeForm = $this->createMock(FormInterface::class);
        $removeForm->expects(self::once())
            ->method('getData')
            ->willReturn($removedUsers);

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::any())
            ->method('getName')
            ->willReturn('formName');
        $form->expects(self::once())
            ->method('submit')
            ->willReturnCallback(function () use ($role, $newCustomer, $form) {
                $role->setCustomer($newCustomer);
                $role->setOrganization($newCustomer->getOrganization());

                return $form;
            });
        $form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);
        $form->expects(self::any())
            ->method('get')
            ->willReturnMap([
                ['appendUsers', $appendForm],
                ['removeUsers', $removeForm],
            ]);

        $this->formFactory->expects(self::once())
            ->method('create')
            ->willReturn($form);

        $this->roleRepository->expects($changeCustomerProcessed ? self::once() : self::never())
            ->method('getAssignedUsers')
            ->with($role)
            ->willReturn($assignedUsers);
    }

    protected function prepareUsersAndRoles(): array
    {
        $oldCustomer = $this->createCustomer(1);
        $newCustomer1 = $this->createCustomer(10);
        $role1 = $this->createCustomerUserRole('test role1', 1);
        $users1 =
            $this->createUsersWithRole($role1, 6, $newCustomer1)
            + $this->createUsersWithRole($role1, 2, $oldCustomer, 6);

        $newCustomer2 = $this->createCustomer(20);
        $oldAcc2 = $this->createCustomer(21);
        $role2 = $this->createCustomerUserRole('test role2', 2);
        $role2->setCustomer($oldAcc2);
        $users2 =
            $this->createUsersWithRole($role2, 6, $newCustomer2)
            + $this->createUsersWithRole($role2, 2, $oldAcc2, 6);

        $role3 = $this->createCustomerUserRole('test role3', 3);
        $role3->setCustomer($this->createCustomer(31));
        $users3 = $this->createUsersWithRole($role3, 6, $role3->getCustomer());

        $newCustomer4 = $this->createCustomer(41);
        $role4 = $this->createCustomerUserRole('test role4', 4);
        $role4->setCustomer($this->createCustomer(40));
        $users4 = $this->createUsersWithRole($role4, 6, $newCustomer4);

        $newCustomer5 = $this->createCustomer(50);
        $role5 = $this->createCustomerUserRole('test role5');
        $role5->setCustomer($this->createCustomer(51));
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
