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
    protected $provider;

    /** @var FormFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $mockFormFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UrlGeneratorInterface
     */
    protected $router;

    protected function setUp(): void
    {
        $this->mockFormFactory = $this->createMock(FormFactoryInterface::class);
        $this->router = $this->createMock(UrlGeneratorInterface::class);

        $this->provider = new FrontendCustomerAddressFormProvider($this->mockFormFactory, $this->router);
    }

    public function testGetAddressFormViewWhileUpdate()
    {
        $this->actionTestWithId(1);
    }

    public function testGetAddressFormViewWhileCreate()
    {
        $this->actionTestWithId();
    }

    /**
     * @param int|null $id
     */
    private function actionTestWithId($id = null)
    {
        $mockCustomerUserAddress = $this->createMock(CustomerAddress::class);
        $mockCustomerUserAddress->expects($this->any())
            ->method('getId')
            ->willReturn($id);

        $mockCustomerUser = $this->createMock(Customer::class);
        $mockCustomerUser->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $mockFormView = $this->createMock(FormView::class);

        $mockForm = $this->createMock(FormInterface::class);
        $mockForm->expects($this->once())
            ->method('createView')
            ->willReturn($mockFormView);

        $this->mockFormFactory->expects($this->once())
            ->method('create')
            ->with(FrontendCustomerTypedAddressType::class, $mockCustomerUserAddress)
            ->willReturn($mockForm);

        $form = $this->provider->getAddressFormView($mockCustomerUserAddress, $mockCustomerUser);

        $this->assertInstanceOf(FormView::class, $form);

        $formSecondCall = $this->provider->getAddressFormView($mockCustomerUserAddress, $mockCustomerUser);
        $this->assertSame($form, $formSecondCall);
    }
}
