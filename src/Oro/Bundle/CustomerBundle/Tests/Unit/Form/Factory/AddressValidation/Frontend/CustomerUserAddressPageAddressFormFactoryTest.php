<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Factory\AddressValidation\Frontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Form\Factory\AddressValidation\Frontend\CustomerUserAddressPageAddressFormFactory;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserTypedAddressType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

final class CustomerUserAddressPageAddressFormFactoryTest extends TestCase
{
    private FormFactoryInterface&MockObject $formFactory;
    private CustomerUserAddressPageAddressFormFactory $addressFormFactory;
    private Request $request;

    #[\Override]
    protected function setUp(): void
    {
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->addressFormFactory = new CustomerUserAddressPageAddressFormFactory($this->formFactory);
        $this->request = new Request();
    }

    public function testCreateAddressForm(): void
    {
        $form = $this->createMock(FormInterface::class);

        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(
                FrontendCustomerUserTypedAddressType::class,
                self::callback(static function ($data) {
                    self::assertInstanceOf(CustomerUserAddress::class, $data);
                    self::assertInstanceOf(CustomerUser::class, $data->getFrontendOwner());

                    return true;
                })
            )
            ->willReturn($form);

        $result = $this->addressFormFactory->createAddressForm($this->request);

        self::assertSame($form, $result);
    }

    public function testCreateAddressFormWhenHasExplicitAddress(): void
    {
        $address = (new CustomerUserAddress())
            ->setFrontendOwner(new CustomerUser());
        $form = $this->createMock(FormInterface::class);

        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(FrontendCustomerUserTypedAddressType::class, $address)
            ->willReturn($form);

        $result = $this->addressFormFactory->createAddressForm($this->request, $address);

        self::assertSame($form, $result);
    }
}
