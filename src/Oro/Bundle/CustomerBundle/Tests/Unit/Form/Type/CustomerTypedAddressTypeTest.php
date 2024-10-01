<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
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
    private AddressType $billingType;
    private AddressType $shippingType;
    private CustomerTypedAddressType $formType;

    #[\Override]
    protected function setUp(): void
    {
        $this->formType = new CustomerTypedAddressType();
        $this->formType->setAddressTypeDataClass(AddressType::class);
        $this->formType->setDataClass(CustomerAddress::class);

        $this->billingType = new AddressType(AddressType::TYPE_BILLING);
        $this->shippingType = new AddressType(AddressType::TYPE_SHIPPING);

        parent::setUp();
    }

    #[\Override]
    protected function getExtensions(): array
    {
        $addressRepository = $this->createMock(EntityRepository::class);
        $addressRepository->expects($this->any())
            ->method('findAll')
            ->willReturn([$this->billingType, $this->shippingType]);
        $addressRepository->expects($this->any())
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

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->any())
            ->method('getRepository')
            ->willReturn($addressRepository);

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
                    ], $em),
                    AddressFormType::class => new AddressTypeStub(),
                ],
                [FormType::class => [new StripTagsExtensionStub($this)]]
            )
        ];
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertEquals('oro_customer_typed_address', $this->formType->getBlockPrefix());
    }

    public function testSubmit(): void
    {
        $addressWithAllDefaultTypes = new CustomerAddress();
        $addressWithAllDefaultTypes->setTypes(new ArrayCollection([$this->billingType, $this->shippingType]));
        $addressWithAllDefaultTypes->setDefaults(new ArrayCollection([$this->billingType, $this->shippingType]));

        $submittedData = [
            'types' => [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING],
            'defaults' => ['default' => [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING]]
        ];

        $form = $this->factory->create(CustomerTypedAddressType::class, null, ['single_form' => false]);
        $this->assertNull($form->getData());
        $this->assertNull($form->getViewData());

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($addressWithAllDefaultTypes, $form->getData());
    }

    public function testSubmitWithSubscribers(): void
    {
        $address1 = new CustomerAddress();
        $address1->setTypes(new ArrayCollection([$this->billingType, $this->shippingType]));

        $address2 = new CustomerAddress();
        $address2->setTypes(new ArrayCollection([$this->billingType, $this->shippingType]));
        $address2->setDefaults(new ArrayCollection([$this->billingType, $this->shippingType]));

        $customer = new Customer();
        $customer->addAddress($address1);
        $customer->addAddress($address2);

        $submittedData = [
            'types' => [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING],
            'defaults' => ['default' => [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING]],
        ];

        $form = $this->factory->create(CustomerTypedAddressType::class, $address1);
        $this->assertSame($address1, $form->getData());
        $this->assertSame($address1, $form->getViewData());

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());

        $expectedBillingType = new AddressType(AddressType::TYPE_BILLING);
        $expectedShippingType = new AddressType(AddressType::TYPE_SHIPPING);
        $addressExpected = new CustomerAddress();
        $addressExpected->setFrontendOwner($customer);
        $addressExpected->setPrimary(true);
        $addressExpected->addType($expectedBillingType);
        $addressExpected->addType($expectedShippingType);
        $addressExpected->setDefaults(new ArrayCollection([$expectedBillingType, $expectedShippingType]));
        $this->assertEquals($addressExpected, $form->getData());

        /** @var AddressType $type */
        foreach ($address2->getDefaults() as $type) {
            $this->assertNotContains($type->getName(), $submittedData['defaults']['default']);
        }
    }
}
