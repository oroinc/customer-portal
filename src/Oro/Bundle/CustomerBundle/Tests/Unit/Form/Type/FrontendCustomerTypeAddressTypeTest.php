<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerTypedAddressType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\AddressTypeStub;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\CustomerTypedAddressWithDefaultTypeStub;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\EntityType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\FrontendOwnerSelectTypeStub;
use Oro\Bundle\FormBundle\Form\Type\Select2Type;
use Oro\Bundle\FormBundle\Tests\Unit\Stub\StripTagsExtensionStub;
use Oro\Bundle\UIBundle\Tools\HtmlTagHelper;
use Symfony\Component\Form\PreloadedExtension;

class FrontendCustomerTypeAddressTypeTest extends CustomerTypedAddressTypeTest
{
    /** @var FrontendCustomerTypedAddressType */
    protected $formType;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->formType = new FrontendCustomerTypedAddressType();
        $this->formType->setAddressTypeDataClass('Oro\Bundle\AddressBundle\Entity\AddressType');
        $this->formType->setDataClass('Oro\Bundle\CustomerBundle\Entity\CustomerAddress');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->formType);

        parent::tearDown();
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
            'translatable_entity'
        );

        $addressTypeStub = new AddressTypeStub();

        return [
            new PreloadedExtension(
                [
                    $addressType->getName() => $addressType,
                    CustomerTypedAddressWithDefaultTypeStub::NAME  => new CustomerTypedAddressWithDefaultTypeStub([
                        $this->billingType,
                        $this->shippingType
                    ], $this->em),
                    FrontendOwnerSelectTypeStub::NAME => new FrontendOwnerSelectTypeStub(),
                    $addressTypeStub->getName()  => $addressTypeStub,
                    'oro_select2_translatable_entity' => new Select2Type(
                        'Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType',
                        'oro_select2_translatable_entity'
                    ),
                ],
                ['form' => [new StripTagsExtensionStub($this->createMock(HtmlTagHelper::class))]]
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
        $form = $this->factory->create($this->formType, $defaultData, $options);
        $this->assertTrue($form->has('frontendOwner'));
        $this->assertEquals($defaultData, $form->getData());
        $this->assertEquals($viewData, $form->getViewData());
        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $expectedData->setFrontendOwner($updateOwner);
        $this->assertEquals($expectedData, $form->getData());
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

        $customer = $this->getCustomer();
        $customer->addAddress($customerAddress1);
        $customer->addAddress($customerAddress2);

        $customerAddressExpected = new CustomerAddress();
        $customerAddressExpected
            ->setPrimary(true)
            ->addType($this->billingType)
            ->addType($this->shippingType)
            ->removeType($this->billingType) // emulate working of forms. It first delete types and after add it
            ->removeType($this->shippingType)
            ->addType($this->billingType)
            ->addType($this->shippingType)
            ->setDefaults(new ArrayCollection([$this->billingType, $this->shippingType]))
            ->setFrontendOwner($customer);

        return [
            'FixCustomerAddressesDefaultSubscriber check' => [
                'options' => [],
                'defaultData' => $customerAddress1,
                'viewData' => $customerAddress1,
                'submittedData' => [
                    'types' => [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING],
                    'defaults' => ['default' => [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING]],
                    'primary' => true,
                    'frontendOwner' => $customer
                ],
                'expectedData' => $customerAddressExpected,
                'otherAddresses' => [$customerAddress2],
                'updateOwner' => $customer
            ]
        ];
    }

    public function testSubmitWithoutPrimary()
    {
        $customerAddress1 = new CustomerAddress();
        $customerAddress1
            ->setTypes(new ArrayCollection([$this->billingType, $this->shippingType]));

        $customer = $this->getCustomer();
        $customer->addAddress($customerAddress1);

        $submittedData = [
            'types' => [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING],
            'defaults' => ['default' => [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING]],
            'frontendOwner' => $customer
        ];

        $form = $this->factory->create($this->formType, $customerAddress1, []);
        $this->assertTrue($form->has('frontendOwner'));
        $this->assertFalse($form->has('primary'));
        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
    }

    public function testGetName()
    {
        $this->assertInternalType('string', $this->formType->getName());
        $this->assertEquals('oro_customer_frontend_typed_address', $this->formType->getName());
    }

    /**
     * @return Customer
     */
    protected function getCustomer()
    {
        return new Customer();
    }
}
