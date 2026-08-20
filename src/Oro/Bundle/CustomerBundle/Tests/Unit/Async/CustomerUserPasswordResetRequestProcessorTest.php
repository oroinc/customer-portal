<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Async;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Async\CustomerUserPasswordResetRequestProcessor;
use Oro\Bundle\CustomerBundle\Async\PasswordResetRequestContext;
use Oro\Bundle\CustomerBundle\Async\Topic\CustomerUserPasswordResetRequestTopic;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Manager\LocalizationManager;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CustomerUserPasswordResetRequestProcessorTest extends TestCase
{
    private const WEBSITE_ID = 42;
    private const LOCALIZATION_ID = 4242;
    private const TTL = 86400;

    private CustomerUserManager&MockObject $userManager;
    private ObjectManager&MockObject $objectManager;
    private LocalizationManager&MockObject $localizationManager;
    private PasswordResetRequestContext $passwordResetRequestContext;
    private LoggerInterface&MockObject $logger;
    private CustomerUserPasswordResetRequestProcessor $processor;
    private ?Website $currentWebsite = null;

    #[\Override]
    protected function setUp(): void
    {
        $this->userManager = $this->createMock(CustomerUserManager::class);
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->localizationManager = $this->createMock(LocalizationManager::class);
        $this->passwordResetRequestContext = new PasswordResetRequestContext();
        $this->logger = $this->createMock(LoggerInterface::class);

        $websiteManager = $this->createMock(WebsiteManager::class);
        $websiteManager->expects(self::any())
            ->method('getCurrentWebsite')
            ->willReturnCallback(fn () => $this->currentWebsite);
        $websiteManager->expects(self::any())
            ->method('setCurrentWebsite')
            ->willReturnCallback(function (?Website $website) {
                $this->currentWebsite = $website;
            });

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects(self::any())
            ->method('getManagerForClass')
            ->with(Website::class)
            ->willReturn($this->objectManager);

        $this->processor = new CustomerUserPasswordResetRequestProcessor(
            $this->userManager,
            $websiteManager,
            $this->localizationManager,
            $this->passwordResetRequestContext,
            $doctrine,
            $this->logger,
            self::TTL
        );
    }

    public function testSubscribedTopics(): void
    {
        self::assertEquals(
            [CustomerUserPasswordResetRequestTopic::getName()],
            CustomerUserPasswordResetRequestProcessor::getSubscribedTopics()
        );
    }

    public function testProcessWithNonExistentUser(): void
    {
        $website = $this->expectWebsiteFound();

        $this->expectUserLookupWithinWebsiteContext('nonexisting@example.com', $website, null);

        $this->localizationManager->expects(self::never())
            ->method('getLocalization');

        $this->userManager->expects(self::never())
            ->method('sendResetPasswordEmail');

        $this->logger->expects(self::never())
            ->method('error');

        self::assertEquals(MessageProcessorInterface::ACK, $this->process('nonexisting@example.com'));
    }

    public function testProcessSendsResetPasswordEmail(): void
    {
        $user = new CustomerUser();
        $website = $this->expectWebsiteFound();
        $localization = $this->expectLocalizationFound();

        $this->expectUserLookupWithinWebsiteContext('test@example.com', $website, $user);
        $this->expectResetPasswordEmailSentWithinLocalizationContext($user, $localization);

        self::assertEquals(MessageProcessorInterface::ACK, $this->process('test@example.com'));
    }

    public function testProcessWhenPasswordAlreadyRequestedWithinTokenLifetime(): void
    {
        $user = new CustomerUser();
        $user->setEmail('test@example.com');
        $user->setPasswordRequestedAt(new \DateTime('now', new \DateTimeZone('UTC')));

        $website = $this->expectWebsiteFound();

        $this->expectUserLookupWithinWebsiteContext('test@example.com', $website, $user);

        $this->localizationManager->expects(self::never())
            ->method('getLocalization');

        $this->userManager->expects(self::never())
            ->method('sendResetPasswordEmail');

        $this->logger->expects(self::once())
            ->method('notice')
            ->with(
                'The password for this user has already been requested within the last 24 hours.',
                ['email' => 'test@example.com']
            );

        self::assertEquals(MessageProcessorInterface::ACK, $this->process('test@example.com'));
    }

    public function testProcessWhenPasswordRequestTokenExpired(): void
    {
        $user = new CustomerUser();
        $user->setPasswordRequestedAt(new \DateTime('-2 days', new \DateTimeZone('UTC')));

        $website = $this->expectWebsiteFound();
        $localization = $this->expectLocalizationFound();

        $this->expectUserLookupWithinWebsiteContext('test@example.com', $website, $user);
        $this->expectResetPasswordEmailSentWithinLocalizationContext($user, $localization);

        $this->logger->expects(self::never())
            ->method('notice');

        self::assertEquals(MessageProcessorInterface::ACK, $this->process('test@example.com'));
    }

    public function testProcessWhenNoWebsiteAndLocalizationInMessage(): void
    {
        $user = new CustomerUser();

        $this->objectManager->expects(self::never())
            ->method('find');

        $this->localizationManager->expects(self::never())
            ->method('getLocalization');

        $this->expectUserLookupWithinWebsiteContext('test@example.com', null, $user);
        $this->expectResetPasswordEmailSentWithinLocalizationContext($user, null);

        self::assertEquals(MessageProcessorInterface::ACK, $this->process('test@example.com', null, null));
    }

    public function testProcessWhenWebsiteNotFound(): void
    {
        $this->objectManager->expects(self::once())
            ->method('find')
            ->with(Website::class, self::WEBSITE_ID)
            ->willReturn(null);

        $this->logger->expects(self::once())
            ->method('warning')
            ->with(
                self::stringContains('website with id {website_id} is not found'),
                ['website_id' => self::WEBSITE_ID]
            );

        $this->expectUserLookupWithinWebsiteContext('test@example.com', null, null);

        $this->userManager->expects(self::never())
            ->method('sendResetPasswordEmail');

        self::assertEquals(MessageProcessorInterface::ACK, $this->process('test@example.com', self::WEBSITE_ID, null));
    }

    public function testProcessWhenLocalizationNotFound(): void
    {
        $user = new CustomerUser();
        $website = $this->expectWebsiteFound();

        $this->localizationManager->expects(self::once())
            ->method('getLocalization')
            ->with(self::LOCALIZATION_ID)
            ->willReturn(null);

        $this->logger->expects(self::once())
            ->method('warning')
            ->with(
                self::stringContains('localization with id {localization_id} is not found'),
                ['localization_id' => self::LOCALIZATION_ID]
            );

        $this->expectUserLookupWithinWebsiteContext('test@example.com', $website, $user);
        $this->expectResetPasswordEmailSentWithinLocalizationContext($user, null);

        self::assertEquals(MessageProcessorInterface::ACK, $this->process('test@example.com'));
    }

    public function testProcessWithEmailSendingError(): void
    {
        $user = new CustomerUser();
        $exception = new \Exception('Mailer is not available');
        $website = $this->expectWebsiteFound();
        $this->expectLocalizationFound();

        $this->expectUserLookupWithinWebsiteContext('test@example.com', $website, $user);

        $this->userManager->expects(self::once())
            ->method('sendResetPasswordEmail')
            ->with($user)
            ->willThrowException($exception);

        $this->logger->expects(self::once())
            ->method('error')
            ->with(
                'Unable to sent the reset password email.',
                ['email' => 'test@example.com', 'exception' => $exception]
            );

        self::assertEquals(MessageProcessorInterface::REJECT, $this->process('test@example.com'));
    }

    private function expectWebsiteFound(): Website
    {
        $website = new Website();
        $this->objectManager->expects(self::once())
            ->method('find')
            ->with(Website::class, self::WEBSITE_ID)
            ->willReturn($website);

        return $website;
    }

    private function expectLocalizationFound(): Localization
    {
        $localization = new Localization();
        $this->localizationManager->expects(self::once())
            ->method('getLocalization')
            ->with(self::LOCALIZATION_ID)
            ->willReturn($localization);

        return $localization;
    }

    private function expectUserLookupWithinWebsiteContext(
        string $email,
        ?Website $expectedWebsite,
        ?CustomerUser $user
    ): void {
        $this->userManager->expects(self::once())
            ->method('findUserByUsernameOrEmail')
            ->with($email)
            ->willReturnCallback(function () use ($expectedWebsite, $user) {
                self::assertSame($expectedWebsite, $this->currentWebsite);

                return $user;
            });
    }

    private function expectResetPasswordEmailSentWithinLocalizationContext(
        CustomerUser $user,
        ?Localization $expectedLocalization
    ): void {
        $this->userManager->expects(self::once())
            ->method('sendResetPasswordEmail')
            ->with($user)
            ->willReturnCallback(function () use ($expectedLocalization) {
                self::assertSame($expectedLocalization, $this->passwordResetRequestContext->getLocalization());
            });
    }

    private function process(
        string $userIdentifier,
        ?int $websiteId = self::WEBSITE_ID,
        ?int $localizationId = self::LOCALIZATION_ID
    ): string {
        $this->currentWebsite = new Website();

        $message = new Message();
        $message->setBody([
            CustomerUserPasswordResetRequestTopic::USER_IDENTIFIER => $userIdentifier,
            CustomerUserPasswordResetRequestTopic::WEBSITE_ID => $websiteId,
            CustomerUserPasswordResetRequestTopic::LOCALIZATION_ID => $localizationId,
        ]);

        $result = $this->processor->process($message, $this->createMock(SessionInterface::class));

        self::assertNull($this->currentWebsite);
        self::assertNull($this->passwordResetRequestContext->getLocalization());

        return $result;
    }
}
