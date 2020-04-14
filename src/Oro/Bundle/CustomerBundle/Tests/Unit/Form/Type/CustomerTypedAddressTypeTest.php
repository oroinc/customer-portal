<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Form\Type\AddressType as AddressFormType;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerTypedAddressType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerTypedAddressWithDefaultType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\AddressTypeStub;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\CustomerTypedAddressWithDefaultTypeStub;
use Oro\Bundle\FormBundle\Tests\Unit\Stub\StripTagsExtensionStub;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class CustomerTypedAddressTypeTest extends FormIntegrationTestCase
{
    /** @var CustomerTypedAddressType */
    protected $formType;

    /** @var AddressType */
    protected $billingType;

    /** @var AddressType */
    protected $shippingType;

    /** @var \PHPUnit\Framework\MockObject\MockObject|EntityManager */
    protected $em;

    /** @var \PHPUnit\Framework\MockObject\MockObject|EntityRepository */
    protected $addressRepository;

    /**
     * {@inheritdoc}
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        // We need they in data provider, so we should create they here
        $this->billingType = new AddressType(AddressType::TYPE_BILLING);
        $this->shippingType = new AddressType(AddressType::TYPE_SHIPPING);

        $this->em = $this->createEntityManagerMock();
        $this->addressRepository = $this->createRepositoryMock([
            $this->billingType,
            $this->shippingType
        ]);
        $this->addressRepository->expects($this->any())
            ->method('findBy')
            ->will($this->returnCallback(function ($params) {
                $result = [];
                foreach ($params['name'] as $name) {
                    switch ($name) {
                        case AddressType::TYPE_BILLING:
                            $result[] = $this->billingType;
                            break;
                        case AddressType::TYPE_SHIPPING:
                            $result[] = $this->shippingType;
                            break;
                    }
                }

                return $result;
            }));
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->formType = new CustomerTypedAddressType();
        $this->formType->setAddressTypeDataClass('Oro\Bundle\AddressBundle\Entity\AddressType');
        $this->formType->setDataClass('Oro\Bundle\CustomerBundle\Entity\CustomerAddress');
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
        $addressType = new EntityType(
            [
                AddressType::TYPE_BILLING => $this->billingType,
                AddressType::TYPE_SHIPPING => $this->shippingType,
            ],
            TranslatableEntityType::NAME
        );

        $addressTypeStub = new AddressTypeStub();

        return [
            new PreloadedExtension(
                [
                    $this->formType,
                    TranslatableEntityType::class => $addressType,
                    CustomerTypedAddressWithDefaultType::class  => new CustomerTypedAddressWithDefaultTypeStub([
                        $this->billingType,
                        $this->shippingType
                    ], $this->em),
                    AddressFormType::class => $addressTypeStub,
                ],
                [FormType::class => [new StripTagsExtensionStub($this)]]
            )
        ];
    }

    /**
     * @param array $options
     * @param mixed $defaultData
     * @param mixed $viewData
     * @param mixed $submittedData
     * @param mixed $expectedData
     * @param null  $updateOwner
     * @dataProvider submitDataProvider
     */
    public function testSubmit(
        array $options,
        $defaultData,
        $viewData,
        $submittedData,
        $expectedData,
        $updateOwner = null
    ) {
        $form = $this->factory->create(CustomerTypedAddressType::class, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());
        $this->assertEquals($viewData, $form->getViewData());

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        if (is_object($expectedData) && $updateOwner) {
            $expectedData->setFrontendOwner($updateOwner);
        }
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function submitDataProvider()
    {
        $customerAddressWithAllDefaultTypes = new CustomerAddress();
        $customerAddressWithAllDefaultTypes
            ->setPrimary(true)
            ->setTypes(new ArrayCollection([$this->billingType, $this->shippingType]))
            ->setDefaults(new ArrayCollection([$this->billingType, $this->shippingType]));

        return [
            'all default types' => [
                'options' => ['single_form' => false],
                'defaultData' => null,
                'viewData' => null,
                'submittedData' => [
                    'types' => [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING],
                    'defaults' => ['default' => [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING]],
                    'primary' => true,
                ],
                'expectedData' => $customerAddressWithAllDefaultTypes,
                'updateOwner' => [],
            ],
        ];
    }

    /**
     * @dataProvider submitWithFormSubscribersProvider
     * @param array $options
     * @param       $defaultData
     * @param       $viewData
     * @param       $submittedData
     * @param       $expectedData
     * @param       $otherAddresses
     * @param null  $updateOwner
     */
    public function testSubmitWithSubscribers(
        array $options,
        $defaultData,
        $viewData,
        $submittedData,
        $expectedData,
        $otherAddresses,
        $updateOwner = null
    ) {
        $this->testSubmit($options, $defaultData, $viewData, $submittedData, $expectedData, $updateOwner);

        /** @var CustomerAddress $otherAddress */
        foreach ($otherAddresses as $otherAddress) {
            /** @var AddressType $otherDefaultType */
            foreach ($otherAddress->getDefaults() as $otherDefaultType) {
                $this->assertNotContains($otherDefaultType->getName(), $submittedData['defaults']['default']);
            }
        }
    }

    /**
     * @return array
     */
    public function submitWithFormSubscribersProvider()
    {
        $customerAddress1 = new CustomerAddress();
        $customerAddress1
            ->setTypes(new ArrayCollection([$this->billingType, $this->shippingType]));

        $customerAddress2 = new CustomerAddress();
        $customerAddress2
            ->setTypes(new ArrayCollection([$this->billingType, $this->shippingType]))
            ->setDefaults(new ArrayCollection([$this->billingType, $this->shippingType]));

        $customerAddressExpected = new CustomerAddress();
        $customerAddressExpected
            ->setPrimary(true)
            ->addType($this->billingType)
            ->addType($this->shippingType)
            ->removeType($this->billingType) // emulate working of forms. It first delete types and after add it
            ->removeType($this->shippingType)
            ->addType($this->billingType)
            ->addType($this->shippingType)
            ->setDefaults(new ArrayCollection([$this->billingType, $this->shippingType]));

        $customer = new Customer();
        $customer->addAddress($customerAddress1);
        $customer->addAddress($customerAddress2);

        return [
            'FixCustomerAddressesDefaultSubscriber check' => [
                'options' => [],
                'defaultData' => $customerAddress1,
                'viewData' => $customerAddress1,
                'submittedData' => [
                    'types' => [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING],
                    'defaults' => ['default' => [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING]],
                    'primary' => true,
                ],
                'expectedData' => $customerAddressExpected,
                'otherAddresses' => [$customerAddress2],
                'updateOwner' => $customer
            ]
        ];
    }

    public function testGetName()
    {
        $this->assertIsString($this->formType->getName());
        $this->assertEquals('oro_customer_typed_address', $this->formType->getName());
    }

    /**
     * @param array $entityModels
     * @return \PHPUnit\Framework\MockObject\MockObject|EntityRepository
     */
    protected function createRepositoryMock(array $entityModels = [])
    {
        $repo = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->any())
            ->method('findAll')
            ->will($this->returnValue($entityModels));

        $this->em->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repo));

        return $repo;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|EntityManager
     */
    protected function createEntityManagerMock()
    {
        return $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
