<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRoleSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendOwnerSelectType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\AddressCollectionTypeStub;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\EntitySelectTypeStub;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\FrontendOwnerSelectTypeStub;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;

class FrontendCustomerUserTypeTest extends CustomerUserTypeTest
{
    /**
     * @var FrontendCustomerUserType
     */
    protected $formType;

    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $authorizationChecker;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $tokenAccessor;

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $websiteManager;

    /**
     * {@inheritdoc}
     */
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

        FormIntegrationTestCase::setUp();
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $customer = $this->getCustomer(1);
        $user = new CustomerUser();
        $user->setCustomer($customer);
        $this->tokenAccessor->expects($this->any())->method('getUser')->willReturn($user);

        $frontendUserRoleSelectType = new EntitySelectTypeStub(
            $this->getRoles(),
            FrontendCustomerUserRoleSelectType::NAME,
            new CustomerUserRoleSelectType($this->createTranslator())
        );
        $addressEntityType = new EntityType($this->getAddresses(), 'test_address_entity');
        $customerSelectType = new EntityType($this->getCustomers(), CustomerSelectType::NAME);

        $customerUserType = new CustomerUserType($this->authorizationChecker, $this->tokenAccessor);
        $customerUserType->setDataClass(CustomerUser::class);
        $customerUserType->setAddressClass(self::ADDRESS_CLASS);

        return [
            new PreloadedExtension(
                [
                    FrontendCustomerUserType::class => $this->formType,
                    CustomerUserType::class => $customerUserType,
                    FrontendCustomerUserRoleSelectType::class => $frontendUserRoleSelectType,
                    CustomerSelectType::class => $customerSelectType,
                    FrontendOwnerSelectType::class => new FrontendOwnerSelectTypeStub(),
                    AddressCollectionType::class => new AddressCollectionTypeStub(),
                    EntityType::class => $addressEntityType,
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    /**
     * @dataProvider submitProvider
     *
     * @param CustomerUser $defaultData
     * @param array $submittedData
     * @param CustomerUser $expectedData
     * @param bool $roleGranted
     */
    public function testSubmit(
        CustomerUser $defaultData,
        array $submittedData,
        CustomerUser $expectedData,
        $roleGranted = true
    ) {
        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->willReturnCallback(function ($acl) {
                if ($acl === 'oro_customer_customer_user_role_view') {
                    return false;
                }

                return true;
            });

        $form = $this->factory->create(FrontendCustomerUserType::class, $defaultData, []);

        $this->assertEquals($defaultData, $form->getData());
        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitProvider()
    {
        $newCustomerUser = new CustomerUser();
        $customer = new Customer();
        $newCustomerUser->setCustomer($customer);
        $existingCustomerUser = new CustomerUser();

        $class = new \ReflectionClass($existingCustomerUser);
        $prop = $class->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($existingCustomerUser, 42);

        $existingCustomerUser->setFirstName('John');
        $existingCustomerUser->setLastName('Doe');
        $existingCustomerUser->setEmail('johndoe@example.com');
        $existingCustomerUser->setPassword('123456');
        $existingCustomerUser->setCustomer($customer);
        $existingCustomerUser->addAddress($this->getAddresses()[1]);

        $alteredExistingCustomerUser = clone $existingCustomerUser;
        $alteredExistingCustomerUser->setEnabled(false);
        $alteredExistingCustomerUser->setCustomer($customer);

        $alteredExistingCustomerUserWithRole = clone $alteredExistingCustomerUser;
        $alteredExistingCustomerUserWithRole->setRoles([$this->getRole(2, 'test02')]);

        $alteredExistingCustomerUserWithAddresses = clone $alteredExistingCustomerUser;
        $alteredExistingCustomerUserWithAddresses->addAddress($this->getAddresses()[2]);

        return
            [
                'user without submitted data' => [
                    'defaultData' => $newCustomerUser,
                    'submittedData' => [],
                    'expectedData' => $newCustomerUser,
                ],
                'altered existing user' => [
                    'defaultData' => $existingCustomerUser,
                    'submittedData' => [
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'email' => 'johndoe@example.com',
                        'customer' => $existingCustomerUser->getCustomer()->getName(),
                    ],
                    'expectedData' => $alteredExistingCustomerUser,
                ],
                'altered existing user with roles' => [
                    'defaultData' => $existingCustomerUser,
                    'submittedData' => [
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'email' => 'johndoe@example.com',
                        'customer' => $existingCustomerUser->getCustomer()->getName(),
                        'roles' => [2],
                    ],
                    'expectedData' => $alteredExistingCustomerUserWithRole,
                    'altered existing user with addresses' => [
                        'defaultData' => $existingCustomerUser,
                        'submittedData' => [
                            'firstName' => 'John',
                            'lastName' => 'Doe',
                            'email' => 'johndoe@example.com',
                            'customer' => $alteredExistingCustomerUserWithRole->getCustomer()->getName(),
                            'addresses' => [1, 2],
                        ],
                        'expectedData' => $alteredExistingCustomerUserWithAddresses,
                    ],
                ],
            ];
    }

    /**
     * Test getName
     */
    public function testGetName()
    {
        $this->assertEquals(FrontendCustomerUserType::NAME, $this->formType->getName());
    }

    /**
     * @depends testSubmit
     */
    public function testOnPreSetData()
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $formType = new FrontendCustomerUserType(
            $authorizationChecker,
            $tokenAccessor,
            $this->websiteManager
        );

        $event = $this->getMockBuilder(FormEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tokenAccessor->expects($this->any())->method('getUser')->willReturn(null);

        $formType->onPreSetData($event);
    }

    /**
     * @dataProvider onSubmitDataProvider
     * @param int|null $customerUserId
     * @param Website|null $website
     * @param Website|null $expectedWebsite
     */
    public function testOnSubmit($customerUserId, Website $website = null, Website $expectedWebsite = null)
    {
        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->willReturn(false);

        $this->websiteManager->expects($this->any())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $customer = new Customer();
        $newCustomerUser = $this->getEntity(CustomerUser::class, $customerUserId);
        $newCustomerUser->setCustomer($customer);
        $form = $this->factory->create(FrontendCustomerUserType::class, $newCustomerUser, []);

        $form->submit([]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedWebsite, $form->getData()->getWebsite());
    }

    /**
     * @return array
     */
    public function onSubmitDataProvider()
    {
        $website = new Website();

        return [
            'no id with website' => [null, $website, $website],
            'id with website' => [1, new Website(), null],
            'id without website' => [1, null, null],
            'no id without website' => [null, null, null],
        ];
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|TranslatorInterface
     */
    private function createTranslator()
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(
                function ($message) {
                    return $message . '.trans';
                }
            );

        return $translator;
    }
}
