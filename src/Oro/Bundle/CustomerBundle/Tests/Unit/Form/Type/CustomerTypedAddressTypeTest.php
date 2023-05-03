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
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityTypeStub;
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

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        // We need they in data provider, so we should create they here
        $this->billingType = new AddressType(AddressType::TYPE_BILLING);
        $this->shippingType = new AddressType(AddressType::TYPE_SHIPPING);

        $this->addressRepository = $this->createMock(EntityRepository::class);
        $this->addressRepository->expects($this->any())
            ->method('findAll')
            ->willReturn([$this->billingType, $this->shippingType]);

        $this->addressRepository->expects($this->any())
            ->method('findBy')
            ->willReturnCallback(function ($params) {
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
            });

        $this->em = $this->createMock(EntityManager::class);
        $this->em->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->addressRepository);
    }

    protected function setUp(): void
    {
        $this->formType = new CustomerTypedAddressType();
        $this->formType->setAddressTypeDataClass(AddressType::class);
        $this->formType->setDataClass(CustomerAddress::class);
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
                    TranslatableEntityType::class => new EntityTypeStub([
                        AddressType::TYPE_BILLING => $this->billingType,
                        AddressType::TYPE_SHIPPING => $this->shippingType,
                    ]),
                    CustomerTypedAddressWithDefaultType::class  => new CustomerTypedAddressWithDefaultTypeStub([
                        $this->billingType,
                        $this->shippingType
                    ], $this->em),
                    AddressFormType::class => new AddressTypeStub(),
                ],
                [FormType::class => [new StripTagsExtensionStub($this)]]
            )
        ];
    }

    /**
     * @dataProvider submitDataProvider
     */
    public function testSubmit(
        array $options,
        mixed $defaultData,
        mixed $viewData,
        mixed $submittedData,
        mixed $expectedData,
        mixed $updateOwner = null
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function submitDataProvider(): array
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
     */
    public function testSubmitWithSubscribers(
        array $options,
        mixed $defaultData,
        mixed $viewData,
        mixed $submittedData,
        mixed $expectedData,
        mixed $otherAddresses,
        mixed $updateOwner = null
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

    public function submitWithFormSubscribersProvider(): array
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
}
