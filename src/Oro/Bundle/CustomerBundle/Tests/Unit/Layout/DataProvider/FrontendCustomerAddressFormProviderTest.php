<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerTypedAddressType;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerAddressFormProvider;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FrontendCustomerAddressFormProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var FrontendCustomerAddressFormProvider */
    private $provider;

    /** @var FormFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $formFactory;

    /** @var \PHPUnit\Framework\MockObject\MockObject|UrlGeneratorInterface */
    private $router;

    protected function setUp(): void
    {
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->router = $this->createMock(UrlGeneratorInterface::class);

        $this->provider = new FrontendCustomerAddressFormProvider($this->formFactory, $this->router);
    }

    public function testGetAddressFormViewWhileUpdate()
    {
        $this->actionTestWithId(1);
    }

    public function testGetAddressFormViewWhileCreate()
    {
        $this->actionTestWithId();
    }

    private function actionTestWithId(?int $id = null): void
    {
        $customerUserAddress = $this->createMock(CustomerAddress::class);
        $customerUserAddress->expects($this->any())
            ->method('getId')
            ->willReturn($id);

        $customerUser = $this->createMock(Customer::class);
        $customerUser->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $formView = $this->createMock(FormView::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('createView')
            ->willReturn($formView);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(FrontendCustomerTypedAddressType::class, $customerUserAddress)
            ->willReturn($form);

        $form = $this->provider->getAddressFormView($customerUserAddress, $customerUser);

        $this->assertInstanceOf(FormView::class, $form);

        $formSecondCall = $this->provider->getAddressFormView($customerUserAddress, $customerUser);
        $this->assertSame($form, $formSecondCall);
    }
}
