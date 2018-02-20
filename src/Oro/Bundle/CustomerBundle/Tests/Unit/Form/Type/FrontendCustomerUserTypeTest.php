<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRoleSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\AddressCollectionTypeStub;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\EntitySelectTypeStub;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\EntityType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\FrontendOwnerSelectTypeStub;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType as CustomerSelectTypeStub;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validation;

class FrontendCustomerUserTypeTest extends CustomerUserTypeTest
{
    const DATA_CLASS = 'Oro\Bundle\CustomerBundle\Entity\CustomerUser';

    /**
     * @var FrontendCustomerUserType
     */
    protected $formType;

    /** @var AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $authorizationChecker;

    /** @var  TokenAccessorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $tokenAccessor;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->formType = new FrontendCustomerUserType($this->authorizationChecker, $this->tokenAccessor);
        $this->formType->setCustomerUserClass(self::DATA_CLASS);
        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->getFormFactory();
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
        $customerSelectType = new CustomerSelectTypeStub($this->getCustomers(), 'oro_customer_customer_select');

        $customerUserType = new CustomerUserType($this->authorizationChecker, $this->tokenAccessor);
        $customerUserType->setDataClass(self::DATA_CLASS);
        $customerUserType->setAddressClass(self::ADDRESS_CLASS);

        return [
            new PreloadedExtension(
                [
                    OroDateType::NAME => new OroDateType(),
                    CustomerUserType::NAME => $customerUserType,
                    FrontendCustomerUserRoleSelectType::NAME => $frontendUserRoleSelectType,
                    $customerSelectType->getName() => $customerSelectType,
                    FrontendOwnerSelectTypeStub::NAME => new FrontendOwnerSelectTypeStub(),
                    AddressCollectionTypeStub::NAME => new AddressCollectionTypeStub(),
                    $addressEntityType->getName() => $addressEntityType,
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

        $form = $this->factory->create($this->formType, $defaultData, []);

        $this->assertEquals($defaultData, $form->getData());
        $form->submit($submittedData);
        $result = $form->isValid();
        $this->assertTrue($result);
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

        $formType = new FrontendCustomerUserType($authorizationChecker, $tokenAccessor);

        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $tokenAccessor->expects($this->any())->method('getUser')->willReturn(null);

        $formType->onPreSetData($event);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TranslatorInterface
     */
    private function createTranslator()
    {
        $translator = $this->createMock('Symfony\Component\Translation\TranslatorInterface');
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
