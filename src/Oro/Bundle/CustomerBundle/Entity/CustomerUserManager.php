<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Mailer\Processor;
use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\UserBundle\Entity\BaseUserManager;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Oro\Bundle\UserBundle\Security\UserLoaderInterface;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\DependencyInjection\ServiceLink;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

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
    private EnumValueProvider $enumValueProvider;
    private LoggerInterface $logger;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        UserLoaderInterface $userLoader,
        ManagerRegistry $doctrine,
        EncoderFactoryInterface $encoderFactory,
        ConfigManager $configManager,
        ServiceLink $emailProcessor,
        FrontendHelper $frontendHelper,
        LocalizationHelper $localizationHelper,
        WebsiteManager $websiteManager,
        EnumValueProvider $enumValueProvider,
        LoggerInterface $logger
    ) {
        parent::__construct($userLoader, $doctrine, $encoderFactory);
        $this->configManager = $configManager;
        $this->emailProcessorLink = $emailProcessor;
        $this->frontendHelper = $frontendHelper;
        $this->localizationHelper = $localizationHelper;
        $this->websiteManager = $websiteManager;
        $this->enumValueProvider = $enumValueProvider;
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
                sprintf('Unable to send welcome notification email to user "%s"', $user->getUsername())
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
                    $user->getUsername()
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
                sprintf('Unable to send confirmation email to user "%s"', $user->getUsername())
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
            $this->enumValueProvider->getEnumValueByCode(
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
            $defaultStatus = $this->enumValueProvider->getDefaultEnumValueByCode(self::AUTH_STATUS_ENUM_CODE);
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
