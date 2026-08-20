<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\CustomerBundle\Async\Topic\CustomerUserPasswordResetRequestTopic;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserPasswordRequestHandler;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManagerInterface;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\MessageQueueBundle\Test\Unit\MessageQueueExtension;
use Oro\Bundle\UserBundle\Provider\UserLoggingInfoProviderInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomerUserPasswordRequestHandlerTest extends TestCase
{
    use MessageQueueExtension;

    private const WEBSITE_ID = 42;
    private const LOCALIZATION_ID = 4242;

    private UserLoggingInfoProviderInterface&MockObject $userLoggingInfoProvider;
    private LoggerInterface&MockObject $logger;
    private WebsiteManager&MockObject $websiteManager;
    private UserLocalizationManagerInterface&MockObject $userLocalizationManager;
    private FormInterface&MockObject $form;
    private Request&MockObject $request;
    private CustomerUserPasswordRequestHandler $handler;

    #[\Override]
    protected function setUp(): void
    {
        $this->userLoggingInfoProvider = $this->createMock(UserLoggingInfoProviderInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->userLocalizationManager = $this->createMock(UserLocalizationManagerInterface::class);

        $this->handler = new CustomerUserPasswordRequestHandler(
            self::getMessageProducer(),
            $this->userLoggingInfoProvider,
            $this->logger,
            $this->websiteManager,
            $this->userLocalizationManager
        );

        $this->form = $this->createMock(FormInterface::class);
        $this->request = $this->createMock(Request::class);
    }

    public function testProcessWithGetRequest(): void
    {
        $this->request->expects(self::once())
            ->method('isMethod')
            ->with(Request::METHOD_POST)
            ->willReturn(false);

        $this->form->expects(self::never())
            ->method('handleRequest');

        self::assertNull($this->handler->process($this->form, $this->request));
        self::assertMessagesEmpty(CustomerUserPasswordResetRequestTopic::getName());
    }

    public function testProcessWithNotSubmittedForm(): void
    {
        $this->request->expects(self::once())
            ->method('isMethod')
            ->with(Request::METHOD_POST)
            ->willReturn(true);

        $this->form->expects(self::once())
            ->method('handleRequest')
            ->with($this->request);

        $this->form->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(false);

        self::assertNull($this->handler->process($this->form, $this->request));
        self::assertMessagesEmpty(CustomerUserPasswordResetRequestTopic::getName());
    }

    public function testProcessWithInvalidForm(): void
    {
        $this->request->expects(self::once())
            ->method('isMethod')
            ->with(Request::METHOD_POST)
            ->willReturn(true);

        $this->form->expects(self::once())
            ->method('handleRequest')
            ->with($this->request);

        $this->form->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(false);

        self::assertNull($this->handler->process($this->form, $this->request));
        self::assertMessagesEmpty(CustomerUserPasswordResetRequestTopic::getName());
    }

    /**
     * @dataProvider submittedEmailDataProvider
     */
    public function testProcessSchedulesProcessingOfSubmittedEmail(string $email): void
    {
        $this->assertValidFormCall($email);

        $website = new Website();
        ReflectionUtil::setId($website, self::WEBSITE_ID);
        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $localization = new Localization();
        ReflectionUtil::setId($localization, self::LOCALIZATION_ID);
        $this->userLocalizationManager->expects(self::once())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        $this->userLoggingInfoProvider->expects(self::once())
            ->method('getUserLoggingInfo')
            ->with($email)
            ->willReturn(['username' => $email, 'ipaddress' => '127.0.0.1']);

        $this->logger->expects(self::once())
            ->method('notice')
            ->with(
                'Reset password email has been requested.',
                ['username' => $email, 'ipaddress' => '127.0.0.1']
            );

        self::assertEquals($email, $this->handler->process($this->form, $this->request));
        self::assertMessageSent(
            CustomerUserPasswordResetRequestTopic::getName(),
            [
                CustomerUserPasswordResetRequestTopic::USER_IDENTIFIER => $email,
                CustomerUserPasswordResetRequestTopic::WEBSITE_ID => self::WEBSITE_ID,
                CustomerUserPasswordResetRequestTopic::LOCALIZATION_ID => self::LOCALIZATION_ID,
            ]
        );
    }

    public function submittedEmailDataProvider(): array
    {
        return [
            'existing customer user' => ['email' => 'existing@example.com'],
            'non-existing customer user' => ['email' => 'nonexisting@example.com'],
        ];
    }

    public function testProcessWhenNoCurrentWebsiteAndLocalization(): void
    {
        $email = 'test@example.com';
        $this->assertValidFormCall($email);

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn(null);

        $this->userLocalizationManager->expects(self::once())
            ->method('getCurrentLocalization')
            ->willReturn(null);

        self::assertEquals($email, $this->handler->process($this->form, $this->request));
        self::assertMessageSent(
            CustomerUserPasswordResetRequestTopic::getName(),
            [
                CustomerUserPasswordResetRequestTopic::USER_IDENTIFIER => $email,
                CustomerUserPasswordResetRequestTopic::WEBSITE_ID => null,
                CustomerUserPasswordResetRequestTopic::LOCALIZATION_ID => null,
            ]
        );
    }

    private function assertValidFormCall(string $email): void
    {
        $this->request->expects(self::once())
            ->method('isMethod')
            ->with(Request::METHOD_POST)
            ->willReturn(true);

        $this->form->expects(self::once())
            ->method('handleRequest')
            ->with($this->request);
        $this->form->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $this->form->expects(self::once())
            ->method('get')
            ->with('email')
            ->willReturn($this->createConfiguredMock(FormInterface::class, ['getData' => $email]));
    }
}
