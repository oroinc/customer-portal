<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Factory\AddressValidation\Frontend;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Form\Factory\AddressValidation\Frontend\CustomerAddressPageAddressFormFactory;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerTypedAddressType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

final class CustomerAddressPageAddressFormFactoryTest extends TestCase
{
    private FormFactoryInterface&MockObject $formFactory;
    private CustomerAddressPageAddressFormFactory $addressFormFactory;
    private Request $request;

    #[\Override]
    protected function setUp(): void
    {
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->addressFormFactory = new CustomerAddressPageAddressFormFactory($this->formFactory);
        $this->request = new Request();
    }

    public function testCreateAddressForm(): void
    {
        $form = $this->createMock(FormInterface::class);

        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(
                FrontendCustomerTypedAddressType::class,
                self::callback(static function ($data) {
                    self::assertInstanceOf(CustomerAddress::class, $data);
                    self::assertInstanceOf(Customer::class, $data->getFrontendOwner());

                    return true;
                })
            )
            ->willReturn($form);

        $result = $this->addressFormFactory->createAddressForm($this->request);

        self::assertSame($form, $result);
    }

    public function testCreateAddressFormWhenHasExplicitAddress(): void
    {
        $address = (new CustomerAddress())
            ->setFrontendOwner(new Customer());
        $form = $this->createMock(FormInterface::class);

        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(FrontendCustomerTypedAddressType::class, $address)
            ->willReturn($form);

        $result = $this->addressFormFactory->createAddressForm($this->request, $address);

        self::assertSame($form, $result);
    }
}
