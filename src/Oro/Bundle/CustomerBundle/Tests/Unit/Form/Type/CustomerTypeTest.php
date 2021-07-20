<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerGroupSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerType;
use Oro\Bundle\CustomerBundle\Form\Type\ParentCustomerSelectType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\AddressCollectionTypeStub;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumSelectType;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Form\Type\UserMultiSelectType;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType;
use Oro\Component\Testing\Unit\Form\Type\Stub\EnumSelectType as EnumSelectTypeStub;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CustomerTypeTest extends FormIntegrationTestCase
{
    /**
     * @var CustomerType
     */
    protected $formType;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var CustomerAddress[]
     */
    protected static $addresses;

    /**
     * @var User[]
     */
    protected static $users;

    /**
     * @var  EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventDispatcher;

    /**
     * @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $authorizationChecker;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->formType = new CustomerType($this->getEventDispatcher(), $this->authorizationChecker);
        $this->formType->setAddressClass('Oro\Bundle\CustomerBundle\Entity\CustomerAddress');

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset($this->formType);
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $customerGroupSelectType = new EntityType(
            [
                1 => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\CustomerGroup', 1),
                2 => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\CustomerGroup', 2)
            ],
            CustomerGroupSelectType::NAME
        );

        $parentCustomerSelectType = new EntityType(
            [
                1 => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\Customer', 1),
                2 => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\Customer', 2)
            ],
            ParentCustomerSelectType::NAME
        );

        $addressEntityType = new EntityType($this->getAddresses(), EntityType::class);

        $internalRatingEnumSelect = new EnumSelectTypeStub(
            [
                new TestEnumValue('1_of_5', '1 of 5'),
                new TestEnumValue('2_of_5', '2 of 5')
            ]
        );

        $userMultiSelectType = new EntityType(
            $this->getUsers(),
            UserMultiSelectType::NAME,
            [
                'class' => 'Oro\Bundle\UserBundle\Entity\User',
                'multiple' => true
            ]
        );

        return [
            new PreloadedExtension(
                [
                    CustomerType::class => $this->formType,
                    CustomerGroupSelectType::class => $customerGroupSelectType,
                    ParentCustomerSelectType::class => $parentCustomerSelectType,
                    AddressCollectionType::class => new AddressCollectionTypeStub(),
                    EntityType::class => $addressEntityType,
                    EnumSelectType::class => $internalRatingEnumSelect,
                    UserMultiSelectType::class => $userMultiSelectType,
                ],
                []
            )
        ];
    }

    /**
     * @dataProvider submitDataProvider
     *
     * @param array $options
     * @param array $defaultData
     * @param array $viewData
     * @param array $submittedData
     * @param array $expectedData
     * @param bool $addressGranted
     */
    public function testSubmit(
        array $options,
        array $defaultData,
        array $viewData,
        array $submittedData,
        array $expectedData,
        $addressGranted = true
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
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function submitDataProvider()
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
                    'group' => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\CustomerGroup', 1),
                    'parent' => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\Customer', 2),
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
                    'group' => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\CustomerGroup', 1),
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
                    'parent' => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\Customer', 2),
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
                    'group' => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\CustomerGroup', 1),
                    'parent' => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\Customer', 2),
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
                    'group' => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\CustomerGroup', 1),
                    'parent' => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\Customer', 2),
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
                    'group' => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\CustomerGroup', 1),
                    'parent' => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\Customer', 2),
                    'internal_rating' => null,
                    'salesRepresentatives' => [],
                ],
                'addressGranted' => false
            ],
        ];
    }

    /**
     * @param string $className
     * @param int $id
     * @return object
     */
    protected function getEntity($className, $id)
    {
        $entity = new $className;

        $reflectionClass = new \ReflectionClass($className);
        $method = $reflectionClass->getProperty('id');
        $method->setAccessible(true);
        $method->setValue($entity, $id);

        return $entity;
    }

    /**
     * @return CustomerAddress[]
     */
    protected function getAddresses()
    {
        if (!self::$addresses) {
            self::$addresses = [
                1 => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\CustomerAddress', 1),
                2 => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\CustomerAddress', 2)
            ];
        }

        return self::$addresses;
    }

    /**
     * @return User[]
     */
    protected function getUsers()
    {
        if (!self::$users) {
            self::$users = [
                1 => $this->getEntity('Oro\Bundle\UserBundle\Entity\User', 1),
                2 => $this->getEntity('Oro\Bundle\UserBundle\Entity\User', 2)
            ];
        }

        return self::$users;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        if (!$this->eventDispatcher) {
            $this->eventDispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        }

        return $this->eventDispatcher;
    }
}
