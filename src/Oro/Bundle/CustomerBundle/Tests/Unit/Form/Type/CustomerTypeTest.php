<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerGroupSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerType;
use Oro\Bundle\CustomerBundle\Form\Type\ParentCustomerSelectType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\AddressCollectionTypeStub;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumSelectType;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Form\Type\Stub\EnumSelectTypeStub;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Form\Type\UserMultiSelectType;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityTypeStub;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CustomerTypeTest extends FormIntegrationTestCase
{
    /** @var CustomerType */
    private $formType;

    /** @var CustomerAddress[] */
    private static $addresses;

    /** @var User[] */
    private static $users;

    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->formType = new CustomerType(
            $this->createMock(EventDispatcherInterface::class),
            $this->authorizationChecker
        );
        $this->formType->setAddressClass(CustomerAddress::class);

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
                    CustomerGroupSelectType::class => new EntityTypeStub([
                        1 => $this->getCustomerGroup(1),
                        2 => $this->getCustomerGroup(2)
                    ]),
                    ParentCustomerSelectType::class => new EntityTypeStub([
                        1 => $this->getCustomer(1),
                        2 => $this->getCustomer(2)
                    ]),
                    AddressCollectionType::class => new AddressCollectionTypeStub(),
                    EntityTypeStub::class => new EntityTypeStub($this->getAddresses()),
                    EnumSelectType::class => new EnumSelectTypeStub([
                        new TestEnumValue('1_of_5', '1 of 5'),
                        new TestEnumValue('2_of_5', '2 of 5')
                    ]),
                    UserMultiSelectType::class => new EntityTypeStub(
                        $this->getUsers(),
                        ['class' => User::class, 'multiple' => true]
                    ),
                ],
                []
            )
        ];
    }

    /**
     * @dataProvider submitDataProvider
     */
    public function testSubmit(
        array $options,
        array $defaultData,
        array $viewData,
        array $submittedData,
        array $expectedData,
        bool $addressGranted = true
    ) {
        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->willReturn($addressGranted);

        $form = $this->factory->create(CustomerType::class, $defaultData, $options);

        $formConfig = $form->getConfig();
        $this->assertNull($formConfig->getOption('data_class'));

        $this->assertEquals($defaultData, $form->getData());
        $this->assertEquals($viewData, $form->getViewData());

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function submitDataProvider(): array
    {
        return [
            'default' => [
                'options' => [],
                'defaultData' => [],
                'viewData' => [],
                'submittedData' => [
                    'name' => 'customer_name',
                    'group' => 1,
                    'parent' => 2,
                    'addresses' => [1],
                    'internal_rating' => '2_of_5',
                    'salesRepresentatives' => [1],
                ],
                'expectedData' => [
                    'name' => 'customer_name',
                    'group' => $this->getCustomerGroup(1),
                    'parent' => $this->getCustomer(2),
                    'addresses' => [$this->getAddresses()[1]],
                    'internal_rating' => new TestEnumValue('2_of_5', '2 of 5'),
                    'salesRepresentatives' => [$this->getUsers()[1]],
                ]
            ],
            'empty parent' => [
                'options' => [],
                'defaultData' => [],
                'viewData' => [],
                'submittedData' => [
                    'name' => 'customer_name',
                    'group' => 1,
                    'parent' => null,
                    'addresses' => [1],
                    'internal_rating' => '2_of_5',
                ],
                'expectedData' => [
                    'name' => 'customer_name',
                    'group' => $this->getCustomerGroup(1),
                    'parent' => null,
                    'addresses' => [$this->getAddresses()[1]],
                    'internal_rating' => new TestEnumValue('2_of_5', '2 of 5'),
                    'salesRepresentatives' => [],
                ]
            ],
            'empty group' => [
                'options' => [],
                'defaultData' => [],
                'viewData' => [],
                'submittedData' => [
                    'name' => 'customer_name',
                    'group' => null,
                    'parent' => 2,
                    'addresses' => [1],
                    'internal_rating' => '2_of_5',
                    'salesRepresentatives' => [1, 2],
                ],
                'expectedData' => [
                    'name' => 'customer_name',
                    'group' => null,
                    'parent' => $this->getCustomer(2),
                    'addresses' => [$this->getAddresses()[1]],
                    'internal_rating' => new TestEnumValue('2_of_5', '2 of 5'),
                    'salesRepresentatives' => [$this->getUsers()[1], $this->getUsers()[2]],
                ]
            ],
            'empty address' => [
                'options' => [],
                'defaultData' => [],
                'viewData' => [],
                'submittedData' => [
                    'name' => 'customer_name',
                    'group' => 1,
                    'parent' => 2,
                    'addresses' => null,
                    'internal_rating' => '2_of_5'
                ],
                'expectedData' => [
                    'name' => 'customer_name',
                    'group' => $this->getCustomerGroup(1),
                    'parent' => $this->getCustomer(2),
                    'addresses' => [],
                    'internal_rating' => new TestEnumValue('2_of_5', '2 of 5'),
                    'salesRepresentatives' => [],
                ]
            ],
            'empty internal_rating' => [
                'options' => [],
                'defaultData' => [],
                'viewData' => [],
                'submittedData' => [
                    'name' => 'customer_name',
                    'group' => 1,
                    'parent' => 2,
                    'internal_rating' => ''
                ],
                'expectedData' => [
                    'name' => 'customer_name',
                    'group' => $this->getCustomerGroup(1),
                    'parent' => $this->getCustomer(2),
                    'internal_rating' => null,
                    'addresses' => [],
                    'salesRepresentatives' => [],
                ]
            ],
            'address not granted' => [
                'options' => [],
                'defaultData' => [],
                'viewData' => [],
                'submittedData' => [
                    'name' => 'customer_name',
                    'group' => 1,
                    'parent' => 2
                ],
                'expectedData' => [
                    'name' => 'customer_name',
                    'group' => $this->getCustomerGroup(1),
                    'parent' => $this->getCustomer(2),
                    'internal_rating' => null,
                    'salesRepresentatives' => [],
                ],
                'addressGranted' => false
            ],
        ];
    }

    private function getUser(int $id): User
    {
        $user = new User();
        ReflectionUtil::setId($user, $id);

        return $user;
    }

    private function getCustomer(int $id): Customer
    {
        $customer = new Customer();
        ReflectionUtil::setId($customer, $id);

        return $customer;
    }

    private function getCustomerGroup(int $id): CustomerGroup
    {
        $customerGroup = new CustomerGroup();
        ReflectionUtil::setId($customerGroup, $id);

        return $customerGroup;
    }

    private function getCustomerAddress(int $id): CustomerAddress
    {
        $customerAddress = new CustomerAddress();
        ReflectionUtil::setId($customerAddress, $id);

        return $customerAddress;
    }

    /**
     * @return CustomerAddress[]
     */
    private function getAddresses(): array
    {
        if (!self::$addresses) {
            self::$addresses = [
                1 => $this->getCustomerAddress(1),
                2 => $this->getCustomerAddress(2)
            ];
        }

        return self::$addresses;
    }

    /**
     * @return User[]
     */
    private function getUsers(): array
    {
        if (!self::$users) {
            self::$users = [
                1 => $this->getUser(1),
                2 => $this->getUser(2)
            ];
        }

        return self::$users;
    }
}
