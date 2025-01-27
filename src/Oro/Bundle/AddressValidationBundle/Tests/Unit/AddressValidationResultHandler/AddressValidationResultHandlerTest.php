<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\AddressValidationResultHandler;

use Oro\Bundle\AddressValidationBundle\AddressValidationResultHandler\AddressValidationResultHandler;
use Oro\Bundle\AddressValidationBundle\Tests\Unit\Stub\AddressValidatedAtAwareStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

final class AddressValidationResultHandlerTest extends TestCase
{
    private AddressValidationResultHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new AddressValidationResultHandler();
    }

    public function testHandleAddressValidationRequestWithNoSuggestedAddresses(): void
    {
        $form = $this->createMock(FormInterface::class);
        $formConfig = $this->createMock(FormConfigInterface::class);

        $form
            ->method('getConfig')
            ->willReturn($formConfig);

        $originalAddress = new AddressValidatedAtAwareStub();
        $formConfig
            ->method('getOption')
            ->with('suggested_addresses')
            ->willReturn([]);

        $form
            ->expects(self::once())
            ->method('submit')
            ->with(['address' => '0']);

        $form
            ->expects(self::never())
            ->method('handleRequest');

        $form
            ->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form
            ->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $form
            ->expects(self::once())
            ->method('getData')
            ->willReturn(['address' => $originalAddress]);

        $request = $this->createMock(Request::class);

        $this->handler->handleAddressValidationRequest($form, $request);

        self::assertInstanceOf(\DateTimeInterface::class, $originalAddress->getValidatedAt());
    }

    public function testHandleAddressValidationRequestWithMultipleSuggestedAddresses(): void
    {
        $form = $this->createMock(FormInterface::class);
        $formConfig = $this->createMock(FormConfigInterface::class);

        $form
            ->method('getConfig')
            ->willReturn($formConfig);

        $originalAddress = new AddressValidatedAtAwareStub();
        $selectedAddress = new AddressValidatedAtAwareStub();
        $formConfig
            ->method('getOption')
            ->with('suggested_addresses')
            ->willReturn([$originalAddress, $selectedAddress]);

        $form
            ->expects(self::never())
            ->method('submit');
        $form
            ->expects(self::once())
            ->method('handleRequest');

        $form
            ->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form
            ->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $form
            ->expects(self::once())
            ->method('getData')
            ->willReturn(['address' => $selectedAddress]);

        $request = $this->createMock(Request::class);

        $this->handler->handleAddressValidationRequest($form, $request);

        self::assertNull($originalAddress->getValidatedAt());
        self::assertInstanceOf(\DateTimeInterface::class, $selectedAddress->getValidatedAt());
    }

    public function testHandleAddressValidationRequestWithInvalidForm(): void
    {
        $form = $this->createMock(FormInterface::class);
        $formConfig = $this->createMock(FormConfigInterface::class);

        $form
            ->method('getConfig')
            ->willReturn($formConfig);

        $originalAddress = new AddressValidatedAtAwareStub();
        $selectedAddress = new AddressValidatedAtAwareStub();
        $formConfig
            ->method('getOption')
            ->with('suggested_addresses')
            ->willReturn([$originalAddress, $selectedAddress]);

        $form
            ->expects(self::never())
            ->method('submit');
        $form
            ->expects(self::once())
            ->method('handleRequest');

        $form
            ->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form
            ->expects(self::once())
            ->method('isValid')
            ->willReturn(false);

        $form
            ->expects(self::never())
            ->method('getData');

        $request = $this->createMock(Request::class);

        $this->handler->handleAddressValidationRequest($form, $request);
    }

    public function testHandleAddressValidationRequestWithUnsubmittedForm(): void
    {
        $form = $this->createMock(FormInterface::class);
        $formConfig = $this->createMock(FormConfigInterface::class);

        $form
            ->method('getConfig')
            ->willReturn($formConfig);

        $originalAddress = new AddressValidatedAtAwareStub();
        $selectedAddress = new AddressValidatedAtAwareStub();
        $formConfig
            ->method('getOption')
            ->with('suggested_addresses')
            ->willReturn([$originalAddress, $selectedAddress]);

        $form
            ->expects(self::never())
            ->method('submit');
        $form
            ->expects(self::once())
            ->method('handleRequest');

        $form
            ->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(false);

        $form
            ->expects(self::never())
            ->method('isValid');
        $form
            ->expects(self::never())
            ->method('getData');

        $request = $this->createMock(Request::class);

        $this->handler->handleAddressValidationRequest($form, $request);
    }
}
