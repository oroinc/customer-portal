<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserTypedAddressType;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendCustomerUserTypedAddressTypeTest extends FrontendCustomerTypedAddressTypeTest
{
    /** @var FrontendCustomerUserTypedAddressType */
    protected $formType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formType = new FrontendCustomerUserTypedAddressType();
        $this->formType->setAddressTypeDataClass(AddressType::class);
        $this->formType->setDataClass(CustomerAddress::class);
        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->getFormFactory();
    }

    public function testGetName()
    {
        $this->assertEquals('oro_customer_frontend_customer_user_typed_address', $this->formType->getName());
    }

    public function testConfigureOptions()
    {
        $optionsResolver = new OptionsResolver();

        $this->formType->configureOptions($optionsResolver);

        $this->assertEquals(
            [
                'owner_field_label' => 'oro.customer.frontend.customer_user.entity_label',
                'data_class' => CustomerAddress::class,
                'single_form' => true,
                'all_addresses_property_path' => 'frontendOwner.addresses',
                'ownership_disabled' => true
            ],
            $optionsResolver->resolve()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getTypeClass(): string
    {
        return FrontendCustomerUserTypedAddressType::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomer(): object
    {
        return new CustomerUser();
    }
}
