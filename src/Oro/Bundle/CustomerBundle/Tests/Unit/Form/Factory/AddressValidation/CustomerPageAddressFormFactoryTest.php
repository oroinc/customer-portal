<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Factory\AddressValidation;

use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Form\Factory\AddressValidation\CustomerPageAddressFormFactory;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

final class CustomerPageAddressFormFactoryTest extends TestCase
{
    private FormFactoryInterface&MockObject $formFactory;
    private CustomerPageAddressFormFactory $addressFormFactory;

    #[\Override]
    protected function setUp(): void
    {
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->addressFormFactory = new CustomerPageAddressFormFactory($this->formFactory);
    }

    public function testCreateAddressForm(): void
    {
        $form = $this->createMock(FormInterface::class);
        $addressForm = $this->createMock(FormInterface::class);
        $addressFormEntry = $this->createMock(FormInterface::class);
        $request = Request::create(
            '/',
            Request::METHOD_POST,
            ['oro_customer' => ['addresses' => ['2' => ['street' => 'some address']]]]
        );

        $form->expects(self::once())
            ->method('get')
            ->with('addresses')
            ->willReturn($addressForm);

        $form->expects(self::any())
            ->method('getName')
            ->willReturn('oro_customer');

        $addressForm->expects(self::once())
            ->method('setData')
            ->with([2 => null]);

        $addressForm->expects(self::once())
            ->method('get')
            ->with('2')
            ->willReturn($addressFormEntry);

        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(CustomerType::class)
            ->willReturn($form);

        $result = $this->addressFormFactory->createAddressForm($request);

        self::assertSame($addressFormEntry, $result);
    }

    public function testCreateAddressFormWhenHasExplicitAddress(): void
    {
        $address = new CustomerAddress();
        $form = $this->createMock(FormInterface::class);
        $addressForm = $this->createMock(FormInterface::class);
        $addressFormEntry = $this->createMock(FormInterface::class);
        $request = Request::create(
            '/',
            Request::METHOD_POST,
            ['oro_customer' => ['addresses' => ['2' => ['street' => 'some address']]]]
        );

        $form->expects(self::once())
            ->method('get')
            ->with('addresses')
            ->willReturn($addressForm);

        $form->expects(self::any())
            ->method('getName')
            ->willReturn('oro_customer');

        $addressForm->expects(self::once())
            ->method('setData')
            ->with([2 => $address]);

        $addressForm->expects(self::once())
            ->method('get')
            ->with('2')
            ->willReturn($addressFormEntry);

        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(CustomerType::class)
            ->willReturn($form);

        $result = $this->addressFormFactory->createAddressForm($request, $address);

        self::assertSame($addressFormEntry, $result);
    }

    public function testCreateAddressFormWhenNoSubmittedData(): void
    {
        $form = $this->createMock(FormInterface::class);
        $addressForm = $this->createMock(FormInterface::class);
        $addressFormEntry = $this->createMock(FormInterface::class);
        $request = Request::create('/');

        $form->expects(self::once())
            ->method('get')
            ->with('addresses')
            ->willReturn($addressForm);

        $form->expects(self::any())
            ->method('getName')
            ->willReturn('oro_customer');

        $addressForm->expects(self::once())
            ->method('setData')
            ->with(['0' => null]);

        $addressForm->expects(self::once())
            ->method('get')
            ->with('0')
            ->willReturn($addressFormEntry);

        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(CustomerType::class)
            ->willReturn($form);

        $result = $this->addressFormFactory->createAddressForm($request);

        self::assertSame($addressFormEntry, $result);
    }

    public function testCreateAddressFormWhenSubmittedDataNotArray(): void
    {
        $form = $this->createMock(FormInterface::class);
        $addressForm = $this->createMock(FormInterface::class);
        $addressFormEntry = $this->createMock(FormInterface::class);
        $request = Request::create('/', Request::METHOD_POST, ['oro_customer' => 'not_array']);

        $form->expects(self::once())
            ->method('get')
            ->with('addresses')
            ->willReturn($addressForm);

        $form->expects(self::any())
            ->method('getName')
            ->willReturn('oro_customer');

        $addressForm->expects(self::once())
            ->method('setData')
            ->with(['0' => null]);

        $addressForm->expects(self::once())
            ->method('get')
            ->with('0')
            ->willReturn($addressFormEntry);

        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(CustomerType::class)
            ->willReturn($form);

        $result = $this->addressFormFactory->createAddressForm($request);

        self::assertSame($addressFormEntry, $result);
    }
}
