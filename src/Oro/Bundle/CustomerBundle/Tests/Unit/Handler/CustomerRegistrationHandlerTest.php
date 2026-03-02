<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Handler;

use Oro\Bundle\CustomerBundle\Handler\CustomerRegistrationHandler;
use Oro\Bundle\CustomerBundle\Handler\RegistrationSuccessMessageProviderInterface;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserRegistrationFormProvider;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerRegistrationHandlerTest extends TestCase
{
    private CustomerRegistrationHandler $customerRegistrationHandler;
    private FrontendCustomerUserRegistrationFormProvider&MockObject $registrationFormProvider;
    private FormHandlerInterface&MockObject $customerUserHandler;
    private UpdateHandlerFacade&MockObject $updateHandlerFacade;
    private TranslatorInterface&MockObject $translator;
    private RegistrationSuccessMessageProviderInterface&MockObject $registrationSuccessMessageProvider;

    #[\Override]
    protected function setUp(): void
    {
        $this->registrationFormProvider = $this->createMock(FrontendCustomerUserRegistrationFormProvider::class);
        $this->customerUserHandler = $this->createMock(FormHandlerInterface::class);
        $this->updateHandlerFacade = $this->createMock(UpdateHandlerFacade::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->registrationSuccessMessageProvider = $this->createMock(
            RegistrationSuccessMessageProviderInterface::class
        );

        $this->customerRegistrationHandler = new CustomerRegistrationHandler(
            $this->registrationFormProvider,
            $this->customerUserHandler,
            $this->updateHandlerFacade,
            $this->translator,
            $this->registrationSuccessMessageProvider
        );
    }

    /**
     * @dataProvider handleIsRegistrationDataProvider
     */
    public function testHandleIsRegistrationRequest(Request $request, bool $expectedResult): void
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

    public function testHandleRegistrationUpdate(): void
    {
        $registrationMessage = 'test message';
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

        $this->registrationSuccessMessageProvider->expects($this->once())
            ->method('getRegistrationSuccessMessage')
            ->willReturn($registrationMessage);

        $translatedMessage = $registrationMessage . '_translated';
        $this->translator->expects($this->once())
            ->method('trans')
            ->with($registrationMessage)
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
}
