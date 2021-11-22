<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserRoleUpdateFrontendHandler;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserRoleFormProvider;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FrontendCustomerUserRoleFormProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var CustomerUserRoleUpdateFrontendHandler|\PHPUnit\Framework\MockObject\MockObject */
    private $handler;

    /** @var \PHPUnit\Framework\MockObject\MockObject|UrlGeneratorInterface */
    private $router;

    /** @var FrontendCustomerUserRoleFormProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->handler = $this->createMock(CustomerUserRoleUpdateFrontendHandler::class);
        $this->router = $this->createMock(UrlGeneratorInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);

        $this->provider = new FrontendCustomerUserRoleFormProvider($formFactory, $this->handler, $this->router);
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testGetRoleFormView(CustomerUserRole $role, string $route, array $routeParameters = [])
    {
        $form = $this->assertCustomerUserRoleFormHandlerCalled($role);

        $this->router->expects($this->exactly(2))
            ->method('generate')
            ->with($route, $routeParameters);

        $actual = $this->provider->getRoleFormView($role);

        $this->assertInstanceOf(FormView::class, $actual);
        $this->assertSame($form->createView(), $actual);

        /** test local cache */
        $this->assertSame($actual, $this->provider->getRoleFormView($role));
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testGetRoleForm(CustomerUserRole $role, string $route, array $routeParameters = [])
    {
        $form = $this->assertCustomerUserRoleFormHandlerCalled($role);

        $this->router->expects($this->exactly(2))
            ->method('generate')
            ->with($route, $routeParameters);

        $actual = $this->provider->getRoleForm($role);

        $this->assertInstanceOf(FormInterface::class, $actual);
        $this->assertSame($form, $actual);

        /** test local cache */
        $this->assertSame($actual, $this->provider->getRoleForm($role));
    }

    public function getDataProvider(): array
    {
        return [
            [
                'role' => $this->getEntity(CustomerUserRole::class),
                'route' => 'oro_customer_frontend_customer_user_role_create'
            ],
            [
                'role' => $this->getEntity(CustomerUserRole::class, ['id' => 42]),
                'route' => 'oro_customer_frontend_customer_user_role_update',
                'routeParameters' => ['id' => 42]
            ]
        ];
    }

    private function assertCustomerUserRoleFormHandlerCalled(
        CustomerUserRole $role,
        string $method = 'TEST'
    ): FormInterface {
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

        $this->handler->expects($this->any())
            ->method('createForm')
            ->with($role)
            ->willReturn($form);
        $this->handler->expects($this->any())
            ->method('process')
            ->with($role);

        return $form;
    }
}
