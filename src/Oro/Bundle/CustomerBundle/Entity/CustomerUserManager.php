<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Mailer\Processor;
use Oro\Bundle\EntityExtendBundle\Provider\EnumOptionsProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\UserBundle\Entity\BaseUserManager;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Oro\Bundle\UserBundle\Security\UserLoaderInterface;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\DependencyInjection\ServiceLink;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

/**
 * Provides a set of methods to simplify manage of the CustomerUser entity.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerUserManager extends BaseUserManager
{
    public const STATUS_ACTIVE  = 'active';
    public const STATUS_RESET = 'reset';

    private const AUTH_STATUS_ENUM_CODE = 'cu_auth_status';

    private ConfigManager $configManager;
    private ServiceLink $emailProcessorLink;
    private FrontendHelper $frontendHelper;
    private LocalizationHelper $localizationHelper;
    private WebsiteManager $websiteManager;
    private EnumOptionsProvider $enumOptionsProvider;
    private LoggerInterface $logger;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        UserLoaderInterface $userLoader,
        ManagerRegistry $doctrine,
        PasswordHasherFactoryInterface $passwordHasherFactory,
        ConfigManager $configManager,
        ServiceLink $emailProcessor,
        FrontendHelper $frontendHelper,
        LocalizationHelper $localizationHelper,
        WebsiteManager $websiteManager,
        EnumOptionsProvider $enumOptionsProvider,
        LoggerInterface $logger
    ) {
        parent::__construct($userLoader, $doctrine, $passwordHasherFactory);
        $this->configManager = $configManager;
        $this->emailProcessorLink = $emailProcessor;
        $this->frontendHelper = $frontendHelper;
        $this->localizationHelper = $localizationHelper;
        $this->websiteManager = $websiteManager;
        $this->enumOptionsProvider = $enumOptionsProvider;
        $this->logger = $logger;
    }

    public function register(CustomerUser $user): void
    {
        $this->updateWebsiteSettings($user);

        if ($this->isConfirmationRequired()) {
            $this->sendConfirmationEmail($user);
        } else {
            $this->confirmRegistration($user);
        }
    }

    public function updateWebsiteSettings(CustomerUser $user): void
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            $currentWebsite = $this->websiteManager->getCurrentWebsite();
            $settings = $user->getWebsiteSettings($currentWebsite);
            if (null === $settings) {
                $settings = new CustomerUserSettings($currentWebsite);
            }

            $settings->setLocalization($this->localizationHelper->getCurrentLocalization());

            $user->setWebsiteSettings($settings);
        }
    }

    public function isConfirmationRequired(): bool
    {
        return (bool)$this->configManager->get('oro_customer.confirmation_required');
    }

    public function confirmRegistration(CustomerUser $user): void
    {
        $user->setConfirmed(true)
            ->setConfirmationToken($user->generateToken());

        $sent = $this->getEmailProcessor()->sendWelcomeNotification($user);
        if (!$sent) {
            $this->logger->error(
                sprintf('Unable to send welcome notification email to user "%s"', $user->getUserIdentifier())
            );
        }
    }

    public function confirmRegistrationByAdmin(CustomerUser $user): void
    {
        $user->setConfirmed(true);

        $this->sendWelcomeRegisteredByAdminEmail($user);
    }

    public function sendWelcomeRegisteredByAdminEmail(CustomerUser $user): void
    {
        $user->setConfirmationToken($user->generateToken());

        $sent = $this->getEmailProcessor()->sendWelcomeForRegisteredByAdminNotification($user);
        if (!$sent) {
            $this->logger->error(
                sprintf(
                    'Unable to send welcome notification email for the registered by admin user "%s"',
                    $user->getUserIdentifier()
                )
            );
        }
    }

    public function sendConfirmationEmail(CustomerUser $user): void
    {
        $user->setConfirmed(false)
            ->setConfirmationToken($user->generateToken());

        $this->updateUser($user);

        $sent = $this->getEmailProcessor()->sendConfirmationEmail($user);
        if (!$sent) {
            $this->logger->error(
                sprintf('Unable to send confirmation email to user "%s"', $user->getUserIdentifier())
            );
        }
    }

    public function sendDuplicateEmailNotification(CustomerUser $customerUser): void
    {
        $minimumDateOffset = new \DateTime('now', new \DateTimeZone('UTC'));
        $minimumDateOffset->sub(new \DateInterval('PT24H'));
        $lastNotificationDate = $customerUser->getLastDuplicateNotificationDate();

        // Last notification was sent within last 24h
        if ($lastNotificationDate && $lastNotificationDate > $minimumDateOffset) {
            return;
        }

        $sent = $this->getEmailProcessor()->sendDuplicateEmailNotification($customerUser);
        if ($sent) {
            $customerUser->setLastDuplicateNotificationDate(new \DateTime('now', new \DateTimeZone('UTC')));
            $this->updateUser($customerUser);
        } else {
            $this->logger->error(
                sprintf('Unable to send duplicate notification email to user "%s"', $customerUser->getUserIdentifier())
            );
        }
    }

    public function sendResetPasswordEmail(CustomerUser $user): void
    {
        $user->setConfirmationToken($user->generateToken());
        $this->getEmailProcessor()->sendResetPasswordEmail($user);
        $user->setPasswordRequestedAt(new \DateTime('now', new \DateTimeZone('UTC')));
    }

    /**
     * {@inheritDoc}
     */
    public function findUserBy(array $criteria): ?UserInterface
    {
        return parent::findUserBy(array_merge($criteria, ['isGuest' => false]));
    }

    /**
     * Sets the given authentication status for a customer user.
     */
    public function setAuthStatus(CustomerUser $customerUser, string $authStatus): void
    {
        $customerUser->setAuthStatus(
            $this->enumOptionsProvider->getEnumOptionByCode(
                self::AUTH_STATUS_ENUM_CODE,
                $authStatus
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser(UserInterface $user, bool $flush = true): void
    {
        // make sure user has a default status
        if ($user instanceof CustomerUser && null === $user->getAuthStatus()) {
            $defaultStatus = $this->enumOptionsProvider->getDefaultEnumOptionByCode(self::AUTH_STATUS_ENUM_CODE);
            if (null !== $defaultStatus) {
                $user->setAuthStatus($defaultStatus);
            }
        }

        parent::updateUser($user, $flush);
    }

    private function getEmailProcessor(): Processor
    {
        return $this->emailProcessorLink->getService();
    }
}
