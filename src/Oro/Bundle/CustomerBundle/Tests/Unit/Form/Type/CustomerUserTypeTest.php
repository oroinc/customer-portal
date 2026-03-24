<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\AddressCollectionTypeStub;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\EntitySelectTypeStub;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Form\Type\UserMultiSelectType;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityTypeStub;
use Oro\Component\Testing\Unit\PreloadedExtension;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerUserTypeTest extends FormIntegrationTestCase
{
    private AuthorizationCheckerInterface&MockObject $authorizationChecker;
    private TokenAccessorInterface&MockObject $tokenAccessor;
    private FeatureChecker&MockObject $featureChecker;
    private AclHelper&MockObject $aclHelper;
    private ManagerRegistry&MockObject $managerRegistry;
    private Customer $customer1;
    private Customer $customer2;
    private CustomerUserAddress $customerUserAddress1;
    private CustomerUserAddress $customerUserAddress2;
    private CustomerUserType $formType;

    #[\Override]
    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->featureChecker = $this->createMock(FeatureChecker::class);
        $this->aclHelper = $this->createMock(AclHelper::class);

        $query = $this->createMock(AbstractQuery::class);
        $query->method('getResult')
            ->willReturn([]);

        $qb = $this->createMock(QueryBuilder::class);
        $this->aclHelper->method('apply')
            ->with($qb)
            ->willReturn($query);

        $repository = $this->createMock(CustomerUserRoleRepository::class);
        $repository->method('getAvailableRolesByCustomerUserQueryBuilder')
            ->willReturn($qb);

        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->managerRegistry->method('getRepository')
            ->willReturn($repository);

        $this->formType = new CustomerUserType(
            $this->authorizationChecker,
            $this->tokenAccessor,
            $this->featureChecker,
            $this->aclHelper,
            $this->managerRegistry
        );
        $this->formType->setDataClass(CustomerUser::class);
        $this->formType->setAddressClass(CustomerUserAddress::class);

        $this->customer1 = self::getCustomer(1, 'first');
        $this->customer2 = self::getCustomer(2, 'second');
        $this->customerUserAddress1 = $this->getCustomerUserAddress(1);
        $this->customerUserAddress2 = $this->getCustomerUserAddress(2);

        parent::setUp();
    }

    #[\Override]
    protected function getExtensions(): array
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(function ($message) {
                return $message . '.trans';
            });

        return [
            new PreloadedExtension(
                [
                    $this->formType,
                    CustomerUserRoleSelectType::class => new EntitySelectTypeStub(
                        [
                            1 => $this->getCustomerUserRole(1, 'test01'),
                            2 => $this->getCustomerUserRole(2, 'test02')
                        ],
                        new CustomerUserRoleSelectType($translator)
                    ),
                    CustomerSelectType::class => new EntityTypeStub([
                        $this->customer1->getId() => $this->customer1,
                        $this->customer2->getId() => $this->customer2
                    ]),
                    AddressCollectionType::class => new AddressCollectionTypeStub(),
                    EntityTypeStub::class => new EntityTypeStub([
                        $this->customerUserAddress1->getId() => $this->customerUserAddress1,
                        $this->customerUserAddress2->getId() => $this->customerUserAddress2
                    ]),
                    UserMultiSelectType::class => new EntityTypeStub(
                        [1 => $this->getUser(1), 2 => $this->getUser(2)],
                        ['multiple' => true]
                    ),
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator())
        ];
    }

    private static function getCustomer(int $id, string $name): Customer
    {
        $customer = new Customer();
        ReflectionUtil::setId($customer, $id);
        $customer->setName($name);

        return $customer;
    }

    private function getCustomerUserRole(int $id, string $label): CustomerUserRole
    {
        $role = new CustomerUserRole($label);
        ReflectionUtil::setId($role, $id);
        $role->setLabel($label);

        return $role;
    }

    private function getUser(int $id): User
    {
        $user = new User();
        ReflectionUtil::setId($user, $id);

        return $user;
    }

    private function getCustomerUserAddress(int $id): CustomerUserAddress
    {
        $customerUserAddress = new CustomerUserAddress();
        ReflectionUtil::setId($customerUserAddress, $id);

        return $customerUserAddress;
    }

    private function getExistingCustomerUser(): CustomerUser
    {
        $customerUser = new CustomerUser();
        ReflectionUtil::setId($customerUser, 42);
        $customerUser->setOrganization(new Organization());
        $customerUser->setFirstName('Mary');
        $customerUser->setLastName('Doe');
        $customerUser->setEmail('john@example.com');
        $customerUser->setPassword('123456');
        $customerUser->setCustomer($this->customer1);
        $customerUser->addAddress($this->customerUserAddress1);
        $customerUser->setOrganization(new Organization());
        $customerUser->addSalesRepresentative($this->getUser(1));

        return $customerUser;
    }

    private function assertQueryBuilderCallback(FormInterface $form): void
    {
        $this->assertTrue($form->has('userRoles'));

        $options = $form->get('userRoles')->getConfig()->getOptions();
        $this->assertArrayHasKey('choices', $options);
    }

    public function testHasNoAddress(): void
    {
        $customerUser = new CustomerUser();
        $customerUser->setOrganization(new Organization());

        $this->authorizationChecker->expects($this->exactly(2))
            ->method('isGranted')
            ->withConsecutive(
                ['oro_customer_customer_user_role_view'],
                ['oro_customer_customer_user_address_update']
            )
            ->willReturn(false);

        $form = $this->factory->create(CustomerUserType::class, $customerUser);
        $this->assertFalse($form->has('addresses'));
    }

    public function testPreSetDataSetsOrganizationWhenNotSet(): void
    {
        $newCustomerUser = new CustomerUser();
        $organization = new Organization();

        $this->authorizationChecker->expects($this->atLeastOnce())
            ->method('isGranted')
            ->willReturn(true);

        $this->tokenAccessor->expects($this->once())
            ->method('getOrganization')
            ->willReturn($organization);

        $this->factory->create(CustomerUserType::class, $newCustomerUser);

        $this->assertSame($organization, $newCustomerUser->getOrganization());
    }

    public function testSubmitWithoutSubmittedData(): void
    {
        $newCustomerUser = new CustomerUser();
        $newCustomerUser->setOrganization(new Organization());

        $this->authorizationChecker->expects($this->atLeastOnce())
            ->method('isGranted')
            ->willReturn(true);

        $this->tokenAccessor->expects($this->never())
            ->method('getOrganization');

        $form = $this->factory->create(CustomerUserType::class, $newCustomerUser);
        $this->assertSame($newCustomerUser, $form->getData());
        $this->assertQueryBuilderCallback($form);

        $form->submit([]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($newCustomerUser, $form->getData());

        $this->assertQueryBuilderCallback($form);
    }

    public function testSubmitAlteredExistingUser(): void
    {
        $existingCustomerUser = $this->getExistingCustomerUser();

        $alteredExistingCustomerUser = clone $existingCustomerUser;
        $alteredExistingCustomerUser->setCustomer($this->customer2);
        $alteredExistingCustomerUser->setEnabled(false);

        $this->authorizationChecker->expects($this->atLeastOnce())
            ->method('isGranted')
            ->willReturn(true);

        $this->tokenAccessor->expects($this->never())
            ->method('getOrganization');

        $form = $this->factory->create(CustomerUserType::class, $existingCustomerUser);
        $this->assertSame($existingCustomerUser, $form->getData());
        $this->assertQueryBuilderCallback($form);

        $form->submit([
            'firstName' => 'Mary',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'customer' => 2
        ]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($alteredExistingCustomerUser, $form->getData());

        $this->assertQueryBuilderCallback($form);
    }

    public function testSubmitAlteredExistingUserWithRoles(): void
    {
        $existingCustomerUser = $this->getExistingCustomerUser();

        $alteredExistingCustomerUserWithRole = clone $existingCustomerUser;
        $alteredExistingCustomerUserWithRole->setCustomer($this->customer2);
        $alteredExistingCustomerUserWithRole->setEnabled(false);
        $alteredExistingCustomerUserWithRole->setUserRoles([$this->getCustomerUserRole(2, 'test02')]);

        $this->authorizationChecker->expects($this->atLeastOnce())
            ->method('isGranted')
            ->willReturn(true);

        $this->tokenAccessor->expects($this->never())
            ->method('getOrganization');

        $form = $this->factory->create(CustomerUserType::class, $existingCustomerUser);
        $this->assertSame($existingCustomerUser, $form->getData());
        $this->assertQueryBuilderCallback($form);

        $form->submit([
            'firstName' => 'Mary',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'customer' => 2,
            'userRoles' => [2]
        ]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($alteredExistingCustomerUserWithRole, $form->getData());

        $this->assertQueryBuilderCallback($form);
    }

    public function testSubmitAlteredExistingUserWithAddresses(): void
    {
        $existingCustomerUser = $this->getExistingCustomerUser();

        $alteredExistingCustomerUserWithAddresses = clone $existingCustomerUser;
        $alteredExistingCustomerUserWithAddresses->setCustomer($this->customer2);
        $alteredExistingCustomerUserWithAddresses->setEnabled(false);
        $alteredExistingCustomerUserWithAddresses->setUserRoles([$this->getCustomerUserRole(2, 'test02')]);
        $alteredExistingCustomerUserWithAddresses->addAddress($this->customerUserAddress2);

        $this->authorizationChecker->expects($this->atLeastOnce())
            ->method('isGranted')
            ->willReturn(true);

        $this->tokenAccessor->expects($this->never())
            ->method('getOrganization');

        $form = $this->factory->create(CustomerUserType::class, $existingCustomerUser);
        $this->assertSame($existingCustomerUser, $form->getData());
        $this->assertQueryBuilderCallback($form);

        $form->submit([
            'firstName' => 'Mary',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'customer' => 2,
            'addresses' => [1, 2]
        ]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($alteredExistingCustomerUserWithAddresses, $form->getData());

        $this->assertQueryBuilderCallback($form);
    }

    public function testSubmitAlteredExistingUserWithSalesRepresentatives(): void
    {
        $existingCustomerUser = $this->getExistingCustomerUser();

        $alteredExistingCustomerUserWithSalesRepresentatives = clone $existingCustomerUser;
        $alteredExistingCustomerUserWithSalesRepresentatives->setCustomer($this->customer2);
        $alteredExistingCustomerUserWithSalesRepresentatives->setEnabled(false);
        $alteredExistingCustomerUserWithSalesRepresentatives->setUserRoles([$this->getCustomerUserRole(2, 'test02')]);
        $alteredExistingCustomerUserWithSalesRepresentatives->addAddress($this->customerUserAddress2);
        $alteredExistingCustomerUserWithSalesRepresentatives->addSalesRepresentative($this->getUser(2));

        $this->authorizationChecker->expects($this->atLeastOnce())
            ->method('isGranted')
            ->willReturn(true);

        $this->tokenAccessor->expects($this->never())
            ->method('getOrganization');

        $form = $this->factory->create(CustomerUserType::class, $existingCustomerUser);
        $this->assertSame($existingCustomerUser, $form->getData());
        $this->assertQueryBuilderCallback($form);

        $form->submit([
            'firstName' => 'Mary',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'customer' => 2,
            'salesRepresentatives' => [],
        ]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($alteredExistingCustomerUserWithSalesRepresentatives, $form->getData());

        $this->assertQueryBuilderCallback($form);
    }
}
