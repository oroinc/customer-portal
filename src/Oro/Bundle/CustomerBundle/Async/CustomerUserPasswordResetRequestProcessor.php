<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Async;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Async\Topic\CustomerUserPasswordResetRequestTopic;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Manager\LocalizationManager;
use Oro\Bundle\UserBundle\Async\AbstractPasswordResetRequestProcessor;
use Oro\Bundle\UserBundle\Async\Topic\AbstractPasswordResetRequestTopic;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerInterface;

/**
 * Sends the reset password email for a forgot password request submitted in the storefront.
 */
class CustomerUserPasswordResetRequestProcessor extends AbstractPasswordResetRequestProcessor
{
    public function __construct(
        private readonly CustomerUserManager $userManager,
        private readonly WebsiteManager $websiteManager,
        private readonly LocalizationManager $localizationManager,
        private readonly PasswordResetRequestContext $passwordResetRequestContext,
        private readonly ManagerRegistry $doctrine,
        LoggerInterface $logger,
        int $ttl
    ) {
        parent::__construct($logger, $ttl);
    }

    #[\Override]
    public static function getSubscribedTopics(): array
    {
        return [CustomerUserPasswordResetRequestTopic::getName()];
    }

    #[\Override]
    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $messageBody = $message->getBody();

        $this->websiteManager->setCurrentWebsite(
            $this->getWebsite($messageBody[CustomerUserPasswordResetRequestTopic::WEBSITE_ID])
        );

        try {
            return $this->sendResetPasswordEmail(
                $messageBody[AbstractPasswordResetRequestTopic::USER_IDENTIFIER],
                $messageBody[CustomerUserPasswordResetRequestTopic::LOCALIZATION_ID]
            );
        } finally {
            $this->websiteManager->setCurrentWebsite(null);
            $this->passwordResetRequestContext->setLocalization(null);
        }
    }

    #[\Override]
    protected function getUserLoggingInfo(AbstractUser $user): array
    {
        return ['email' => $user->getUserIdentifier()];
    }

    private function sendResetPasswordEmail(string $email, ?int $localizationId): string
    {
        /** @var CustomerUser|null $user */
        $user = $this->userManager->findUserByUsernameOrEmail($email);
        if (null === $user || $this->isPasswordAlreadyRequested($user)) {
            return self::ACK;
        }

        $this->passwordResetRequestContext->setLocalization($this->getLocalization($localizationId));

        try {
            $this->userManager->sendResetPasswordEmail($user);
        } catch (\Exception $e) {
            $this->logger->error(
                'Unable to sent the reset password email.',
                ['email' => $email, 'exception' => $e]
            );

            return self::REJECT;
        }

        return self::ACK;
    }

    private function getWebsite(?int $websiteId): ?Website
    {
        if (null === $websiteId) {
            return null;
        }

        $website = $this->doctrine->getManagerForClass(Website::class)->find(Website::class, $websiteId);
        if (null === $website) {
            $this->logger->warning(
                'The website with id {website_id} is not found, the reset password request is processed '
                . 'without the website context.',
                ['website_id' => $websiteId]
            );
        }

        return $website;
    }

    private function getLocalization(?int $localizationId): ?Localization
    {
        if (null === $localizationId) {
            return null;
        }

        $localization = $this->localizationManager->getLocalization($localizationId);
        if (null === $localization) {
            $this->logger->warning(
                'The localization with id {localization_id} is not found, the reset password email is sent '
                . 'in the preferred localization of the customer user.',
                ['localization_id' => $localizationId]
            );
        }

        return $localization;
    }
}
