<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInvitation;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserInvitationAcceptType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserInvitationType;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserInvitationFormProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FrontendCustomerUserInvitationFormProviderTest extends TestCase
{
    private FormFactoryInterface&MockObject $formFactory;
    private UrlGeneratorInterface&MockObject $router;
    private FrontendCustomerUserInvitationFormProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->router = $this->createMock(UrlGeneratorInterface::class);
        $this->provider = new FrontendCustomerUserInvitationFormProvider($this->formFactory, $this->router);
    }

    public function testGetInvitationForm(): void
    {
        $invitation = new CustomerUserInvitation();
        $form = $this->createMock(FormInterface::class);
        $action = '/customer/user/invitations/create';

        $this->router->expects(self::exactly(2))
            ->method('generate')
            ->with('oro_customer_frontend_customer_user_invitation_create', [])
            ->willReturn($action);
        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(FrontendCustomerUserInvitationType::class, $invitation, ['action' => $action])
            ->willReturn($form);

        self::assertEquals($form, $this->provider->getInvitationForm($invitation));
        self::assertEquals($form, $this->provider->getInvitationForm($invitation));
    }

    public function testGetInvitationFormView(): void
    {
        $invitation = new CustomerUserInvitation();
        $form = $this->createMock(FormInterface::class);
        $view = new FormView();
        $action = '/customer/user/invitations/create';

        $this->router->expects(self::exactly(2))
            ->method('generate')
            ->with('oro_customer_frontend_customer_user_invitation_create', [])
            ->willReturn($action);
        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(FrontendCustomerUserInvitationType::class, $invitation, ['action' => $action])
            ->willReturn($form);
        $form->expects(self::once())
            ->method('createView')
            ->willReturn($view);

        self::assertEquals($view, $this->provider->getInvitationFormView($invitation));
        self::assertEquals($view, $this->provider->getInvitationFormView($invitation));
    }

    public function testGetAcceptForm(): void
    {
        $customerUser = new CustomerUser();
        $form = $this->createMock(FormInterface::class);
        $token = 'token';
        $action = '/customer/user/invitations/accept/token';

        $this->router->expects(self::exactly(2))
            ->method('generate')
            ->with('oro_customer_frontend_customer_user_invitation_accept', ['token' => $token])
            ->willReturn($action);
        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(CustomerUserInvitationAcceptType::class, $customerUser, ['action' => $action])
            ->willReturn($form);

        self::assertEquals($form, $this->provider->getAcceptForm($customerUser, $token));
        self::assertEquals($form, $this->provider->getAcceptForm($customerUser, $token));
    }

    public function testGetAcceptFormView(): void
    {
        $customerUser = new CustomerUser();
        $form = $this->createMock(FormInterface::class);
        $view = new FormView();
        $token = 'token';
        $action = '/customer/user/invitations/accept/token';

        $this->router->expects(self::exactly(2))
            ->method('generate')
            ->with('oro_customer_frontend_customer_user_invitation_accept', ['token' => $token])
            ->willReturn($action);
        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(CustomerUserInvitationAcceptType::class, $customerUser, ['action' => $action])
            ->willReturn($form);
        $form->expects(self::once())
            ->method('createView')
            ->willReturn($view);

        self::assertEquals($view, $this->provider->getAcceptFormView($customerUser, $token));
        self::assertEquals($view, $this->provider->getAcceptFormView($customerUser, $token));
    }
}
