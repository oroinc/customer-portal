<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Handler\AbstractCustomerUserRoleHandler;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserRoleUpdateFrontendHandler;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CustomerUserRoleUpdateFrontendHandlerTest extends AbstractCustomerUserRoleUpdateHandlerTestCase
{
    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $tokenStorage;

    /** @var CustomerUserRoleUpdateFrontendHandler */
    protected $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getHandler();
        $this->setRequirementsForHandler($this->handler);

        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function getHandler(): AbstractCustomerUserRoleHandler
    {
        if (!$this->handler) {
            $this->handler = new CustomerUserRoleUpdateFrontendHandler(
                $this->formFactory,
                $this->aclCache,
                $this->privilegeConfig
            );
        }

        return $this->handler;
    }

    /**
     * @dataProvider successDataProvider
     */
    public function testOnSuccess(
        CustomerUserRole $role,
        CustomerUserRole $expectedRole,
        CustomerUser $customerUser,
        CustomerUserRole $expectedPredefinedRole = null
    ): void {
        $request = new Request();
        $request->setMethod('POST');

        $isPredefinedRole = $role->isPredefined();
        $form = $this->createMock(FormInterface::class);
        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(
                $this->isType('string'),
                $this->logicalAnd(
                    $this->isType('object'),
                    $this->callback(
                        function ($role) use ($isPredefinedRole, $expectedRole) {
                            if ($isPredefinedRole) {
                                // check if predefined role is duplicated properly
                                self::assertNotEquals($role->getRole(), $expectedRole->getRole());
                                self::assertMatchesRegularExpression(
                                    sprintf('/%s_[A-Z0-9]{2,}/', preg_quote(CustomerUserRole::PREFIX_ROLE)),
                                    $role->getRole()
                                );

                                $role = clone $role;
                                $expectedRole = clone $expectedRole;
                                $role->setRole('', false);
                                $expectedRole->setRole('', false);
                            }
                            self::assertEquals($role, $expectedRole);

                            return true;
                        }
                    )
                ),
                $this->logicalAnd(
                    $this->isType('array'),
                    $this->callback(
                        function ($options) use ($expectedPredefinedRole) {
                            $this->arrayHasKey('predefined_role');
                            self::assertEquals($expectedPredefinedRole, $options['predefined_role']);

                            return true;
                        }
                    )
                )
            )
            ->willReturn($form);

        $this->handler->setRequest($request);
        $this->handler->setTokenStorage($this->tokenStorage);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::any())
            ->method('getUser')
            ->willReturn($customerUser);
        $this->tokenStorage->expects(self::any())
            ->method('getToken')
            ->willReturn($token);

        $this->handler->createForm($role);
    }

    public function successDataProvider(): array
    {
        $customerUser = new CustomerUser();
        $customer = new Customer();
        $customerUser->setCustomer($customer);

        return [
            'edit predefined role should pass it to form' => [
                (new CustomerUserRole('')),
                (new CustomerUserRole(''))->setCustomer($customer),
                $customerUser,
                (new CustomerUserRole('')),
            ],
            'edit customer role should not pass predefined role to form' => [
                (new CustomerUserRole(''))->setCustomer($customer),
                (new CustomerUserRole(''))->setCustomer($customer),
                $customerUser,
            ],
        ];
    }

    /**
     * @dataProvider successDataPrivilegesProvider
     */
    public function testOnSuccessSetPrivileges(
        CustomerUserRole $role,
        CustomerUserRole $expectedRole,
        CustomerUser $customerUser,
        array $existingPrivileges
    ): void {
        $request = new Request();
        $request->setMethod('GET');

        $form = $this->createMock(FormInterface::class);
        $this->formFactory->expects(self::once())
            ->method('create')
            ->willReturn($form);

        $this->handler->setRequest($request);
        $this->handler->setTokenStorage($this->tokenStorage);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::any())
            ->method('getUser')
            ->willReturn($customerUser);
        $this->tokenStorage->expects(self::any())
            ->method('getToken')
            ->willReturn($token);

        $this->handler->createForm($role);

        $roleSecurityIdentity = new RoleSecurityIdentity($expectedRole);
        $privilegeCollection = new ArrayCollection($existingPrivileges);

        $this->privilegeRepository->expects(self::any())
            ->method('getPrivileges')
            ->with($roleSecurityIdentity)
            ->willReturn($privilegeCollection);

        $this->aclManager->expects(self::once())
            ->method('getSid')
            ->with($expectedRole)
            ->willReturn($roleSecurityIdentity);

        $this->ownershipConfigProvider->expects(self::exactly(3))
            ->method('hasConfig')
            ->willReturn(true);

        $privilegesForm = $this->createMock(FormInterface::class);
        $privilegesForm->expects(self::any())
            ->method('setData');
        $form->expects(self::any())
            ->method('get')
            ->willReturn($privilegesForm);

        $metadata = $this->createMock(OwnershipMetadataInterface::class);
        $metadata->expects(self::exactly(2))
            ->method('hasOwner')
            ->willReturnOnConsecutiveCalls(true, false);
        $this->chainMetadataProvider->expects(self::any())
            ->method('getMetadata')
            ->willReturn($metadata);

        $this->handler->process($role);
    }

    public function successDataPrivilegesProvider(): array
    {
        $customerUser = new CustomerUser();
        $customer = new Customer();
        $customerUser->setCustomer($customer);

        $privilege = new AclPrivilege();
        $privilege->setExtensionKey('entity');
        $privilege->setIdentity(new AclPrivilegeIdentity('entity:\stdClass', 'VIEW'));

        $privilege2 = new AclPrivilege();
        $privilege2->setExtensionKey('action');
        $privilege2->setIdentity(new AclPrivilegeIdentity('action:todo', 'FULL'));

        $privilege3 = new AclPrivilege();
        $privilege3->setExtensionKey('entity');
        $privilege3->setIdentity(new AclPrivilegeIdentity('entity:\stdClassNoOwnership', 'VIEW'));

        $privilege4 = new AclPrivilege();
        $privilege4->setExtensionKey('entity');
        $privilege4->setIdentity(
            new AclPrivilegeIdentity('entity:' . ObjectIdentityFactory::ROOT_IDENTITY_TYPE, 'VIEW')
        );

        return [
            'edit predefined role should use privileges form predefined' => [
                (new CustomerUserRole('')),
                (new CustomerUserRole('')),
                $customerUser,
                ['valid' => $privilege, 'action' => $privilege2, 'no_owner' => $privilege3, 'root' => $privilege4],
            ],
            'edit customer role should use own privileges' => [
                (new CustomerUserRole(''))->setCustomer($customer),
                (new CustomerUserRole(''))->setCustomer($customer),
                $customerUser,
                ['valid' => $privilege, 'action' => $privilege2, 'no_owner' => $privilege3, 'root' => $privilege4],
            ],
        ];
    }

    public function testMissingCustomerUser(): void
    {
        $this->expectException(AccessDeniedException::class);
        $request = new Request();
        $request->setMethod('POST');

        $this->handler->setRequest($request);
        $this->handler->setTokenStorage($this->tokenStorage);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::any())
            ->method('getUser')
            ->willReturn(new \stdClass());
        $this->tokenStorage->expects(self::any())
            ->method('getToken')
            ->willReturn($token);

        $this->handler->createForm(new CustomerUserRole(''));
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
}
