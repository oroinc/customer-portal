<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

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
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Form\Type\UserMultiSelectType;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityTypeStub;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerUserTypeTest extends FormIntegrationTestCase
{
    /** @var CustomerUserType */
    protected $formType;

    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $authorizationChecker;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $tokenAccessor;

    /** @var Customer[] */
    private static $customers = [];

    /** @var CustomerUserAddress[] */
    private static $addresses = [];

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->formType = new CustomerUserType($this->authorizationChecker, $this->tokenAccessor);
        $this->formType->setDataClass(CustomerUser::class);
        $this->formType->setAddressClass(CustomerUserAddress::class);

        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    $this->formType,
                    CustomerUserRoleSelectType::class => new EntitySelectTypeStub(
                        $this->getRoles(),
                        new CustomerUserRoleSelectType($this->createTranslator())
                    ),
                    CustomerSelectType::class => new EntityTypeStub($this->getCustomers()),
                    AddressCollectionType::class => new AddressCollectionTypeStub(),
                    EntityTypeStub::class => new EntityTypeStub($this->getAddresses()),
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

    /**
     * @dataProvider submitProvider
     */
    public function testSubmit(
        CustomerUser $defaultData,
        array $submittedData,
        CustomerUser $expectedData,
        bool $rolesGranted = true
    ) {
        if ($rolesGranted) {
            $this->authorizationChecker->expects($this->any())
                ->method('isGranted')
                ->willReturn(true);
        }

        $this->tokenAccessor->expects($this->exactly(2))
            ->method('getOrganization')
            ->willReturn(new Organization());

        $form = $this->factory->create(CustomerUserType::class, $defaultData, []);

        $this->assertTrue($form->has('userRoles'));
        $options = $form->get('userRoles')->getConfig()->getOptions();
        $this->assertArrayHasKey('query_builder', $options);
        $this->assertQueryBuilderCallback($options['query_builder']);

        $this->assertEquals($defaultData, $form->getData());
        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());

        $this->assertTrue($form->has('userRoles'));
        $options = $form->get('userRoles')->getConfig()->getOptions();
        $this->assertArrayHasKey('query_builder', $options);
        $this->assertQueryBuilderCallback($options['query_builder']);
    }

    public function submitProvider(): array
    {
        $newCustomerUser = new CustomerUser();
        $newCustomerUser->setOrganization(new Organization());

        $existingCustomerUser = clone $newCustomerUser;
        ReflectionUtil::setId($existingCustomerUser, 42);

        $existingCustomerUser->setFirstName('Mary');
        $existingCustomerUser->setLastName('Doe');
        $existingCustomerUser->setEmail('john@example.com');
        $existingCustomerUser->setPassword('123456');
        $existingCustomerUser->setCustomer($this->getCustomer(1));
        $existingCustomerUser->addAddress($this->getAddresses()[1]);
        $existingCustomerUser->setOrganization(new Organization());
        $existingCustomerUser->addSalesRepresentative($this->getUser(1));

        $alteredExistingCustomerUser = clone $existingCustomerUser;
        $alteredExistingCustomerUser->setCustomer($this->getCustomer(2));
        $alteredExistingCustomerUser->setEnabled(false);

        $alteredExistingCustomerUserWithRole = clone $alteredExistingCustomerUser;
        $alteredExistingCustomerUserWithRole->setUserRoles([$this->getRole(2, 'test02')]);

        $alteredExistingCustomerUserWithAddresses = clone $alteredExistingCustomerUser;
        $alteredExistingCustomerUserWithAddresses->addAddress($this->getAddresses()[2]);

        $alteredExistingCustomerUserWithSalesRepresentatives = clone $alteredExistingCustomerUser;
        $alteredExistingCustomerUserWithSalesRepresentatives->addSalesRepresentative($this->getUser(2));

        return
            [
                'user without submitted data' => [
                    'defaultData' => $newCustomerUser,
                    'submittedData' => [],
                    'expectedData' => $newCustomerUser
                ],
                'altered existing user' => [
                    'defaultData' => $existingCustomerUser,
                    'submittedData' => [
                        'firstName' => 'Mary',
                        'lastName' => 'Doe',
                        'email' => 'john@example.com',
                        'customer' => 2
                    ],
                    'expectedData' => $alteredExistingCustomerUser
                ],
                'altered existing user with roles' => [
                    'defaultData' => $existingCustomerUser,
                    'submittedData' => [
                        'firstName' => 'Mary',
                        'lastName' => 'Doe',
                        'email' => 'john@example.com',
                        'customer' => 2,
                        'userRoles' => [2]
                    ],
                    'expectedData' => $alteredExistingCustomerUserWithRole,
                    'rolesGranted' => true
                ],
                'altered existing user with addresses' => [
                    'defaultData' => $existingCustomerUser,
                    'submittedData' => [
                        'firstName' => 'Mary',
                        'lastName' => 'Doe',
                        'email' => 'john@example.com',
                        'customer' => 2,
                        'addresses' => [1, 2]
                    ],
                    'expectedData' => $alteredExistingCustomerUserWithAddresses,
                ],
                'altered existing user with salesRepresentatives' => [
                    'defaultData' => $existingCustomerUser,
                    'submittedData' => [
                        'firstName' => 'Mary',
                        'lastName' => 'Doe',
                        'email' => 'john@example.com',
                        'customer' => 2,
                        'salesRepresentatives' => [],
                    ],
                    'expectedData' => $alteredExistingCustomerUserWithSalesRepresentatives,
                ],
            ];
    }

    private function assertQueryBuilderCallback(\Closure $callable): void
    {
        $this->assertIsCallable($callable);

        $repository = $this->createMock(CustomerUserRoleRepository::class);
        $repository->expects($this->once())
            ->method('getAvailableRolesByCustomerUserQueryBuilder');

        $callable($repository);
    }

    public function testHasNoAddress()
    {
        $customerUser = new CustomerUser();
        $customerUser->setOrganization(new Organization());

        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->withConsecutive(
                ['oro_customer_customer_user_role_view'],
                ['oro_customer_customer_user_address_update']
            )
            ->willReturn(false);

        $form = $this->factory->create(get_class($this->formType), $customerUser, []);
        $this->assertFalse($form->has('addresses'));
    }

    /**
     * @return CustomerUserAddress[]
     */
    protected function getAddresses(): array
    {
        if (!self::$addresses) {
            self::$addresses = [
                1 => $this->getCustomerUserAddress(1),
                2 => $this->getCustomerUserAddress(2)
            ];
        }

        return self::$addresses;
    }

    /**
     * @return CustomerUserRole[]
     */
    protected function getRoles(): array
    {
        return [
            1 => $this->getRole(1, 'test01'),
            2 => $this->getRole(2, 'test02')
        ];
    }

    /**
     * @return Customer[]
     */
    protected function getCustomers(): array
    {
        if (!self::$customers) {
            self::$customers = [
                '1' => $this->createCustomer(1, 'first'),
                '2' => $this->createCustomer(2, 'second')
            ];
        }

        return self::$customers;
    }

    protected function getCustomer(int $id): Customer
    {
        $customers = $this->getCustomers();

        return $customers[$id];
    }

    private static function createCustomer(int $id, string $name): Customer
    {
        $customer = new Customer();
        ReflectionUtil::setId($customer, $id);
        $customer->setName($name);

        return $customer;
    }

    protected function getRole(int $id, string $label): CustomerUserRole
    {
        $role = new CustomerUserRole($label);
        ReflectionUtil::setId($role, $id);
        $role->setLabel($label);

        return $role;
    }

    protected function getUser(int $id): User
    {
        $user = new User();
        ReflectionUtil::setId($user, $id);

        return $user;
    }

    protected function getCustomerUserAddress(int $id): CustomerUserAddress
    {
        $customerUserAddress = new CustomerUserAddress();
        ReflectionUtil::setId($customerUserAddress, $id);

        return $customerUserAddress;
    }

    protected function createTranslator(): TranslatorInterface
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(function ($message) {
                return $message . '.trans';
            });

        return $translator;
    }
}
