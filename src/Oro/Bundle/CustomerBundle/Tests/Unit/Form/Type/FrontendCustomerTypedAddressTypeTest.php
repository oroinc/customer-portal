<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Form\Type\AddressType as AddressFormType;
use Oro\Bundle\AddressBundle\Validator\Constraints\NameOrOrganization;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerTypedAddressWithDefaultType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerTypedAddressType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendOwnerSelectType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\AddressTypeStub;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\CustomerTypedAddressWithDefaultTypeStub;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\FrontendOwnerSelectTypeStub;
use Oro\Bundle\FormBundle\Tests\Unit\Stub\StripTagsExtensionStub;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Oro\Bundle\UIBundle\Tools\HtmlTagHelper;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendCustomerTypedAddressTypeTest extends CustomerTypedAddressTypeTest
{
    /** @var FrontendCustomerTypedAddressType */
    protected $formType;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->formType = new FrontendCustomerTypedAddressType();
        $this->formType->setAddressTypeDataClass('Oro\Bundle\AddressBundle\Entity\AddressType');
        $this->formType->setDataClass('Oro\Bundle\CustomerBundle\Entity\CustomerAddress');

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->getFormFactory();
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
                    $this->formType,
                    TranslatableEntityType::class => $addressType,
                    CustomerTypedAddressWithDefaultType::class  => new CustomerTypedAddressWithDefaultTypeStub([
                        $this->billingType,
                        $this->shippingType
                    ], $this->em),
                    FrontendOwnerSelectType::class => new FrontendOwnerSelectTypeStub(),
                    AddressFormType::class => $addressTypeStub,
                ],
                [FormType::class => [new StripTagsExtensionStub($this->createMock(HtmlTagHelper::class))]]
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
        $form = $this->factory->create($this->getTypeClass(), $defaultData, $options);
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

        $form = $this->factory->create($this->getTypeClass(), $customerAddress1, []);
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

    public function testConfigureOptions()
    {
        $optionsResolver = new OptionsResolver();

        $this->formType->configureOptions($optionsResolver);

        $this->assertEquals(
            [
                'constraints' => [
                    new NameOrOrganization()
                ],
                'owner_field_label' => 'oro.customer.frontend.customer.entity_label',
                'data_class' => CustomerAddress::class,
                'single_form' => true,
                'all_addresses_property_path' => 'frontendOwner.addresses',
                'ownership_disabled' => true,
                'validation_groups' => [
                    'Default',
                    'RequireName',
                    'RequireRegion'
                ]
            ],
            $optionsResolver->resolve()
        );
    }

    /**
     * @return string
     */
    protected function getTypeClass(): string
    {
        return FrontendCustomerTypedAddressType::class;
    }

    /**
     * @return Customer
     */
    protected function getCustomer()
    {
        return new Customer();
    }
}
