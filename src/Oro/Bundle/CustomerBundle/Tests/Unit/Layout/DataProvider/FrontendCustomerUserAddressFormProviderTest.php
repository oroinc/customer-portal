<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserTypedAddressType;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserAddressFormProvider;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Test\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FrontendCustomerUserAddressFormProviderTest extends TestCase
{
    use EntityTrait;

    private FrontendCustomerUserAddressFormProvider $provider;
    private FormFactoryInterface&MockObject $formFactory;
    private UrlGeneratorInterface&MockObject $router;

    #[\Override]
    protected function setUp(): void
    {
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->router = $this->createMock(UrlGeneratorInterface::class);

        $this->provider = new FrontendCustomerUserAddressFormProvider($this->formFactory, $this->router);
    }

    public function testGetAddressFormViewWhileUpdate(): void
    {
        $action = 'form_action';

        $customerUser = $this->getEntity(CustomerUser::class, ['id' => 1]);
        $customerUserAddress = $this->getEntity(CustomerUserAddress::class, ['id' => 2]);

        $formView = $this->createMock(FormView::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('createView')
            ->willReturn($formView);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(FrontendCustomerUserTypedAddressType::class, $customerUserAddress, ['action' => $action])
            ->willReturn($form);

        $this->router->expects($this->exactly(2))
            ->method('generate')
            ->with(
                FrontendCustomerUserAddressFormProvider::ACCOUNT_USER_ADDRESS_UPDATE_ROUTE_NAME,
                ['id' => 2, 'entityId' => 1]
            )
            ->willReturn($action);

        $result = $this->provider->getAddressFormView($customerUserAddress, $customerUser);

        $this->assertInstanceOf(FormView::class, $result);

        $resultSecondCall =  $this->provider->getAddressFormView($customerUserAddress, $customerUser);
        $this->assertSame($result, $resultSecondCall);
    }

    public function testGetAddressFormWhileUpdate(): void
    {
        $action = 'form_action';

        $customerUser = $this->getEntity(CustomerUser::class, ['id' => 1]);
        $customerUserAddress = $this->getEntity(CustomerUserAddress::class, ['id' => 2]);

        $form = $this->createMock(FormInterface::class);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(FrontendCustomerUserTypedAddressType::class, $customerUserAddress, ['action' => $action])
            ->willReturn($form);

        $this->router->expects($this->exactly(2))
            ->method('generate')
            ->with(
                FrontendCustomerUserAddressFormProvider::ACCOUNT_USER_ADDRESS_UPDATE_ROUTE_NAME,
                ['id' => 2, 'entityId' => 1]
            )
            ->willReturn($action);

        $result = $this->provider->getAddressForm($customerUserAddress, $customerUser);

        $this->assertInstanceOf(FormInterface::class, $result);

        $resultSecondCall =  $this->provider->getAddressForm($customerUserAddress, $customerUser);
        $this->assertSame($result, $resultSecondCall);
    }

    public function testGetAddressFormViewWhileCreate(): void
    {
        $action = 'form_action';

        $customerUser = $this->getEntity(CustomerUser::class, ['id' => 1]);
        $customerUserAddress = $this->getEntity(CustomerUserAddress::class);

        $formView = $this->createMock(FormView::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('createView')
            ->willReturn($formView);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(FrontendCustomerUserTypedAddressType::class, $customerUserAddress, ['action' => $action])
            ->willReturn($form);

        $this->router->expects($this->exactly(2))
            ->method('generate')
            ->with(
                FrontendCustomerUserAddressFormProvider::ACCOUNT_USER_ADDRESS_CREATE_ROUTE_NAME,
                ['entityId' => 1]
            )
            ->willReturn($action);

        $result = $this->provider->getAddressFormView($customerUserAddress, $customerUser);

        $this->assertInstanceOf(FormView::class, $result);

        $resultSecondCall =  $this->provider->getAddressFormView($customerUserAddress, $customerUser);
        $this->assertSame($result, $resultSecondCall);
    }

    public function testGetAddressFormWhileCreate(): void
    {
        $action = 'form_action';

        $customerUser = $this->getEntity(CustomerUser::class, ['id' => 1]);
        $customerUserAddress = $this->getEntity(CustomerUserAddress::class);

        $form = $this->createMock(FormInterface::class);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(FrontendCustomerUserTypedAddressType::class, $customerUserAddress, ['action' => $action])
            ->willReturn($form);

        $this->router->expects($this->exactly(2))
            ->method('generate')
            ->with(FrontendCustomerUserAddressFormProvider::ACCOUNT_USER_ADDRESS_CREATE_ROUTE_NAME, ['entityId' => 1])
            ->willReturn($action);

        $result = $this->provider->getAddressForm($customerUserAddress, $customerUser);

        $this->assertInstanceOf(FormInterface::class, $result);

        $resultSecondCall =  $this->provider->getAddressForm($customerUserAddress, $customerUser);
        $this->assertSame($result, $resultSecondCall);
    }
}
