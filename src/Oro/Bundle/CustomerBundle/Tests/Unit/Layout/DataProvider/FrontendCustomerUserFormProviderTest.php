<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserPasswordRequestType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserPasswordResetType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendOwnerSelectType;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserFormProvider;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class FrontendCustomerUserFormProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var FormFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $formFactory;

    /** @var UrlGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $router;

    /** @var FrontendCustomerUserFormProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->formFactory = $this->createMock(FormFactory::class);
        $this->router = $this->createMock(UrlGeneratorInterface::class);

        $this->provider = new FrontendCustomerUserFormProvider($this->formFactory, $this->router);
    }

    /**
     * @dataProvider getCustomerUserFormProvider
     */
    public function testGetCustomerUserFormView(CustomerUser $customerUser, string $route, array $routeParameters = [])
    {
        $this->router->expects($this->exactly(2))
            ->method('generate')
            ->with($route, $routeParameters);

        $form = $this->expectsCustomerUserFormHandlerCalled();
        $actual = $this->provider->getCustomerUserFormView($customerUser);

        $this->assertInstanceOf(FormView::class, $actual);
        $this->assertSame($form->createView(), $actual);

        /** test local cache */
        $this->assertSame($actual, $this->provider->getCustomerUserFormView($customerUser));
    }

    /**
     * @dataProvider getCustomerUserFormProvider
     */
    public function testGetCustomerUserForm(CustomerUser $customerUser, string $route, array $routeParameters = [])
    {
        $this->router->expects($this->exactly(2))
            ->method('generate')
            ->with($route, $routeParameters);

        $form = $this->expectsCustomerUserFormHandlerCalled();
        $actual = $this->provider->getCustomerUserForm($customerUser);

        $this->assertInstanceOf(FormInterface::class, $actual);
        $this->assertSame($form, $actual);

        /** test local cache */
        $this->assertSame($actual, $this->provider->getCustomerUserForm($customerUser));
    }

    public function testGetForgotPasswordFormView()
    {
        $formView = $this->createMock(FormView::class);

        $expectedForm = $this->createMock(\Symfony\Component\Form\Test\FormInterface::class);
        $expectedForm->expects($this->once())
            ->method('createView')
            ->willReturn($formView);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(CustomerUserPasswordRequestType::class)
            ->willReturn($expectedForm);

        // Get form without existing data in locale cache
        $data = $this->provider->getForgotPasswordFormView();
        $this->assertInstanceOf(FormView::class, $data);

        // Get form with existing data in locale cache
        $data = $this->provider->getForgotPasswordFormView();
        $this->assertInstanceOf(FormView::class, $data);
    }

    public function testGetForgotPasswordForm()
    {
        $expectedForm = $this->createMock(\Symfony\Component\Form\Test\FormInterface::class);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(CustomerUserPasswordRequestType::class)
            ->willReturn($expectedForm);

        // Get form without existing data in locale cache
        $data = $this->provider->getForgotPasswordForm();
        $this->assertInstanceOf(FormInterface::class, $data);

        // Get form with existing data in locale cache
        $data = $this->provider->getForgotPasswordForm();
        $this->assertInstanceOf(FormInterface::class, $data);
    }

    public function testGetResetPasswordFormView()
    {
        $formView = $this->createMock(FormView::class);

        $expectedForm = $this->createMock(\Symfony\Component\Form\Test\FormInterface::class);
        $expectedForm->expects($this->once())
            ->method('createView')
            ->willReturn($formView);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(CustomerUserPasswordResetType::class)
            ->willReturn($expectedForm);

        // Get form without existing data in locale cache
        $data = $this->provider->getResetPasswordFormView();
        $this->assertInstanceOf(FormView::class, $data);

        // Get form with existing data in locale cache
        $data = $this->provider->getResetPasswordFormView();
        $this->assertInstanceOf(FormView::class, $data);
    }

    public function testGetResetPasswordForm()
    {
        $expectedForm = $this->createMock(\Symfony\Component\Form\Test\FormInterface::class);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(CustomerUserPasswordResetType::class)
            ->willReturn($expectedForm);

        // Get form without existing data in locale cache
        $data = $this->provider->getResetPasswordForm();
        $this->assertInstanceOf(FormInterface::class, $data);

        // Get form with existing data in locale cache
        $data = $this->provider->getResetPasswordForm();
        $this->assertInstanceOf(FormInterface::class, $data);
    }

    public function testGetCustomerUserSelectFormView()
    {
        $form = $this->createMock(FormInterface::class);
        $view = $this->createMock(FormView::class);

        $form->expects($this->once())
            ->method('createView')
            ->willReturn($view);

        $target = new \stdClass();
        $selectedCustomerUser = new CustomerUser();
        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(FrontendOwnerSelectType::class, $selectedCustomerUser, ['targetObject' => $target])
            ->willReturn($form);
        $this->assertSame($view, $this->provider->getCustomerUserSelectFormView($selectedCustomerUser, $target));
    }

    /**
     * @dataProvider getProfileFormProvider
     */
    public function testGetProfileFormView(CustomerUser $customerUser, string $route, array $routeParameters = [])
    {
        $this->router->expects($this->exactly(2))
            ->method('generate')
            ->with($route, $routeParameters);

        $form = $this->expectsCustomerUserProfileFormHandlerCalled();
        $actual = $this->provider->getProfileFormView($customerUser);

        $this->assertInstanceOf(FormView::class, $actual);
        $this->assertSame($form->createView(), $actual);

        /** test local cache */
        $this->assertSame($actual, $this->provider->getProfileFormView($customerUser));
    }

    /**
     * @dataProvider getProfileFormProvider
     */
    public function testGetProfileForm(CustomerUser $customerUser, string $route, array $routeParameters = [])
    {
        $this->router->expects($this->exactly(2))
            ->method('generate')
            ->with($route, $routeParameters);

        $form = $this->expectsCustomerUserProfileFormHandlerCalled();
        $actual = $this->provider->getProfileForm($customerUser);

        $this->assertInstanceOf(FormInterface::class, $actual);
        $this->assertSame($form, $actual);

        /** test local cache */
        $this->assertSame($actual, $this->provider->getProfileForm($customerUser));
    }

    public function getCustomerUserFormProvider(): array
    {
        return [
            [
                'customerUser' => $this->getEntity(CustomerUser::class),
                'route' => 'oro_customer_frontend_customer_user_create'
            ],
            [
                'customerUser' => $this->getEntity(CustomerUser::class, ['id' => 42]),
                'route' => 'oro_customer_frontend_customer_user_update',
                'routeParameters' => ['id' => 42]
            ]
        ];
    }

    public function getProfileFormProvider(): array
    {
        return [
            [
                'customerUser' => $this->getEntity(CustomerUser::class, ['id' => 42]),
                'route' => 'oro_customer_frontend_customer_user_profile_update',
                'routeParameters' => ['id' => 42]
            ]
        ];
    }

    private function expectsCustomerUserFormHandlerCalled(string $method = 'TEST'): FormInterface
    {
        $config = $this->createMock(FormConfigInterface::class);
        $config->expects($this->any())
            ->method('getMethod')
            ->willReturn($method);

        $view = $this->createMock(FormView::class);
        $view->vars = ['multipart' => null];

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->any())
            ->method('getConfig')
            ->willReturn($config);
        $form->expects($this->any())
            ->method('createView')
            ->willReturn($view);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->willReturn($form);

        return $form;
    }

    private function expectsCustomerUserProfileFormHandlerCalled(string $method = 'TEST'): FormInterface
    {
        $config = $this->createMock(FormConfigInterface::class);
        $config->expects($this->any())
            ->method('getMethod')
            ->willReturn($method);

        $view = $this->createMock(FormView::class);
        $view->vars = ['multipart' => null];

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->any())
            ->method('getConfig')
            ->willReturn($config);
        $form->expects($this->any())
            ->method('createView')
            ->willReturn($view);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->willReturn($form);

        return $form;
    }
}
