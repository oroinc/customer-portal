<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Handler\FrontendCustomerUserHandler;
use Oro\Bundle\CustomerBundle\Handler\CustomerRegistrationHandler;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserRegistrationFormProvider;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerRegistrationHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerRegistrationHandler */
    private $customerRegistrationHandler;

    /** @var FrontendCustomerUserRegistrationFormProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $registrationFormProvider;

    /** @var CustomerUserManager|\PHPUnit\Framework\MockObject\MockObject */
    private $customerUserManager;

    /** @var FrontendCustomerUserHandler|\PHPUnit\Framework\MockObject\MockObject */
    private $customerUserHandler;

    /** @var UpdateHandlerFacade|\PHPUnit\Framework\MockObject\MockObject */
    private $updateHandlerFacade;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    protected function setUp(): void
    {
        $this->registrationFormProvider = $this->createMock(FrontendCustomerUserRegistrationFormProvider::class);
        $this->customerUserManager = $this->createMock(CustomerUserManager::class);
        $this->customerUserHandler = $this->createMock(FrontendCustomerUserHandler::class);
        $this->updateHandlerFacade = $this->createMock(UpdateHandlerFacade::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->customerRegistrationHandler = new CustomerRegistrationHandler(
            $this->registrationFormProvider,
            $this->customerUserManager,
            $this->customerUserHandler,
            $this->updateHandlerFacade,
            $this->translator
        );
    }

    /**
     * @dataProvider handleIsRegistrationDataProvider
     */
    public function testHandleIsRegistrationRequest(Request $request, bool $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->customerRegistrationHandler->isRegistrationRequest($request));
    }

    public function handleIsRegistrationDataProvider(): array
    {
        $registrationRequest = new Request();
        $registrationRequest->query->add(['isRegistration' => true]);

        return [
            'not registration' => [
                'request' => new Request(),
                'expectedResult' => false
            ],
            'registration' => [
                'request' => $registrationRequest,
                'expectedResult' => true
            ]
        ];
    }

    /**
     * @dataProvider getHandleRegistrationUpdateDataProvider
     */
    public function testHandleRegistrationUpdate(
        bool $isConfirmationRequired,
        string $registrationMessage
    ) {
        $request = new Request();
        $request->query->add(['isRegistration' => true]);

        $formData = new \stdClass();
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('getData')
            ->willReturn($formData);
        $this->registrationFormProvider->expects($this->once())
            ->method('getRegisterForm')
            ->willReturn($form);

        $this->customerUserManager->expects($this->once())
            ->method('isConfirmationRequired')
            ->willReturn($isConfirmationRequired);

        $translatedMessage = $registrationMessage . '_translated';
        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn($translatedMessage);

        $updateResults = [];
        $this->updateHandlerFacade->expects($this->once())
            ->method('update')
            ->with(
                $formData,
                $form,
                $translatedMessage,
                $request,
                $this->customerUserHandler
            )
            ->willReturn($updateResults);

        $this->assertEquals($updateResults, $this->customerRegistrationHandler->handleRegistration($request));
    }

    public function getHandleRegistrationUpdateDataProvider(): array
    {
        return [
          'not submitted form' => [
              'isConfirmationRequired' => false,
              'registrationMessage' => 'oro.customer.controller.customeruser.registered.message',
              'isFormSubmitted' => false,
              'isFormValidExpectedCount' => 0,
              'isFormValid' => false
          ],
          'submitted and not valid form' => [
              'isConfirmationRequired' => true,
              'registrationMessage' => 'oro.customer.controller.customeruser.registered_with_confirmation.message',
              'isFormSubmitted' => true,
              'isFormValidExpectedCount' => 1,
              'isFormValid' => false
          ],
          'submitted and valid form' => [
              'isConfirmationRequired' => true,
              'registrationMessage' => 'oro.customer.controller.customeruser.registered_with_confirmation.message',
              'isFormSubmitted' => true,
              'isFormValidExpectedCount' => 1,
              'isFormValid' => true
          ]
        ];
    }
}
