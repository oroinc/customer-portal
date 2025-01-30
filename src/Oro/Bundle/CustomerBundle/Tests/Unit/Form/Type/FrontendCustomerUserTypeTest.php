<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRoleSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendOwnerSelectType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\AddressCollectionTypeStub;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\EntitySelectTypeStub;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\FrontendOwnerSelectTypeStub;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityTypeStub;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;

class FrontendCustomerUserTypeTest extends FormIntegrationTestCase
{
    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    private Customer $customer1;
    private Customer $customer2;
    private CustomerUserAddress $customerUserAddress1;
    private CustomerUserAddress $customerUserAddress2;
    private FrontendCustomerUserType $formType;

    #[\Override]
    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->formType = new FrontendCustomerUserType(
            $this->authorizationChecker,
            $this->tokenAccessor,
            $this->websiteManager
        );
        $this->formType->setCustomerUserClass(CustomerUser::class);

        $this->customer1 = $this->getCustomer(1, 'first');
        $this->customer2 = $this->getCustomer(2, 'second');
        $this->customerUserAddress1 = $this->getCustomerUserAddress(1);
        $this->customerUserAddress2 = $this->getCustomerUserAddress(2);

        parent::setUp();
    }

    #[\Override]
    protected function getExtensions(): array
    {
        $user = new CustomerUser();
        $user->setCustomer($this->customer1);
        $this->tokenAccessor->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $customerUserType = new CustomerUserType($this->authorizationChecker, $this->tokenAccessor);
        $customerUserType->setDataClass(CustomerUser::class);
        $customerUserType->setAddressClass(CustomerUserAddress::class);

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
                    $customerUserType,
                    FrontendCustomerUserRoleSelectType::class => new EntitySelectTypeStub(
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
                    FrontendOwnerSelectType::class => new FrontendOwnerSelectTypeStub([
                        $this->customer1->getId() => $this->customer1,
                        $this->customer2->getId() => $this->customer2
                    ]),
                    AddressCollectionType::class => new AddressCollectionTypeStub(),
                    new EntityTypeStub([
                        $this->customerUserAddress1->getId() => $this->customerUserAddress1,
                        $this->customerUserAddress2->getId() => $this->customerUserAddress2
                    ]),
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator()),
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
        $customerUser->setFirstName('John');
        $customerUser->setLastName('Doe');
        $customerUser->setEmail('johndoe@example.com');
        $customerUser->setPassword('123456');
        $customerUser->setCustomer($this->customer1);
        $customerUser->addAddress($this->customerUserAddress1);

        return $customerUser;
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertEquals('oro_customer_frontend_customer_user', $this->formType->getBlockPrefix());
    }

    public function testHasNoAddress(): void
    {
        $customerUser = new CustomerUser();
        $customerUser->setOrganization(new Organization());

        $this->authorizationChecker->expects($this->atLeastOnce())
            ->method('isGranted')
            ->withConsecutive(
                ['oro_customer_customer_user_role_view'],
                ['oro_customer_customer_user_address_update']
            )
            ->willReturn(false);

        $form = $this->factory->create(FrontendCustomerUserType::class, $customerUser);
        $this->assertFalse($form->has('addresses'));
    }

    public function testSubmitWithoutSubmittedData(): void
    {
        $customer = new Customer();

        $newCustomerUser = new CustomerUser();
        $newCustomerUser->setCustomer($customer);

        $this->authorizationChecker->expects($this->atLeastOnce())
            ->method('isGranted')
            ->willReturnCallback(function ($acl) {
                return $acl !== 'oro_customer_customer_user_role_view';
            });

        $form = $this->factory->create(FrontendCustomerUserType::class, $newCustomerUser);
        $this->assertSame($newCustomerUser, $form->getData());

        $form->submit([]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($newCustomerUser, $form->getData());
    }

    public function testSubmitAlteredExistingUser(): void
    {
        $existingCustomerUser = $this->getExistingCustomerUser();

        $alteredExistingCustomerUser = clone $existingCustomerUser;
        $alteredExistingCustomerUser->setEnabled(false);

        $this->authorizationChecker->expects($this->atLeastOnce())
            ->method('isGranted')
            ->willReturnCallback(function ($acl) {
                return $acl !== 'oro_customer_customer_user_role_view';
            });

        $form = $this->factory->create(FrontendCustomerUserType::class, $existingCustomerUser);
        $this->assertSame($existingCustomerUser, $form->getData());

        $form->submit([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'johndoe@example.com',
            'customer' => $existingCustomerUser->getCustomer()->getId(),
        ]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($alteredExistingCustomerUser, $form->getData());
    }

    public function testSubmitAlteredExistingUserWithRoles(): void
    {
        $existingCustomerUser = $this->getExistingCustomerUser();

        $alteredExistingCustomerUserWithRole = clone $existingCustomerUser;
        $alteredExistingCustomerUserWithRole->setEnabled(false);
        $alteredExistingCustomerUserWithRole->setUserRoles([$this->getCustomerUserRole(2, 'test02')]);

        $this->authorizationChecker->expects($this->atLeastOnce())
            ->method('isGranted')
            ->willReturnCallback(function ($acl) {
                return $acl !== 'oro_customer_customer_user_role_view';
            });

        $form = $this->factory->create(FrontendCustomerUserType::class, $existingCustomerUser);
        $this->assertSame($existingCustomerUser, $form->getData());

        $form->submit([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'johndoe@example.com',
            'customer' => $existingCustomerUser->getCustomer()->getId(),
            'userRoles' => [2],
        ]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($alteredExistingCustomerUserWithRole, $form->getData());
    }

    public function testOnPreSetData(): void
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $formType = new FrontendCustomerUserType(
            $authorizationChecker,
            $tokenAccessor,
            $this->websiteManager
        );

        $event = $this->createMock(FormEvent::class);

        $tokenAccessor->expects($this->any())
            ->method('getUser')
            ->willReturn(null);

        $formType->onPreSetData($event);
    }

    /**
     * @dataProvider onSubmitDataProvider
     */
    public function testOnSubmit(?int $customerUserId, ?Website $website = null, ?Website $expectedWebsite = null): void
    {
        $this->authorizationChecker->expects($this->atLeastOnce())
            ->method('isGranted')
            ->willReturn(false);

        $this->websiteManager->expects($this->any())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $customer = new Customer();
        $newCustomerUser = new CustomerUser();
        ReflectionUtil::setId($newCustomerUser, $customerUserId);
        $newCustomerUser->setCustomer($customer);
        $form = $this->factory->create(FrontendCustomerUserType::class, $newCustomerUser);

        $form->submit([]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedWebsite, $form->getData()->getWebsite());
    }

    public function onSubmitDataProvider(): array
    {
        $website = new Website();

        return [
            'no id with website' => [null, $website, $website],
            'id with website' => [1, new Website(), null],
            'id without website' => [1, null, null],
            'no id without website' => [null, null, null],
        ];
    }
}
