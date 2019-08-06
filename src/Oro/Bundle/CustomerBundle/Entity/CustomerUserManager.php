<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Mailer\Processor;
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
    /** @var ConfigManager */
    private $configManager;

    /** @var ServiceLink */
    private $emailProcessorLink;

    /** @var FrontendHelper */
    private $frontendHelper;

    /** @var LocalizationHelper */
    private $localizationHelper;

    /** @var WebsiteManager */
    private $websiteManager;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param UserLoaderInterface     $userLoader
     * @param ManagerRegistry         $doctrine
     * @param EncoderFactoryInterface $encoderFactory
     * @param ConfigManager           $configManager
     * @param ServiceLink             $emailProcessor
     * @param FrontendHelper          $frontendHelper
     * @param LocalizationHelper      $localizationHelper
     * @param WebsiteManager          $websiteManager
     * @param LoggerInterface         $logger
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
        LoggerInterface $logger
    ) {
        parent::__construct($userLoader, $doctrine, $encoderFactory);
        $this->configManager = $configManager;
        $this->emailProcessorLink = $emailProcessor;
        $this->frontendHelper = $frontendHelper;
        $this->localizationHelper = $localizationHelper;
        $this->websiteManager = $websiteManager;
        $this->logger = $logger;
    }

    /**
     * @param CustomerUser $user
     */
    public function register(CustomerUser $user): void
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

        if ($this->isConfirmationRequired()) {
            $this->sendConfirmationEmail($user);
        } else {
            $this->confirmRegistration($user);
        }
    }

    /**
     * @return bool
     */
    public function isConfirmationRequired(): bool
    {
        return (bool)$this->configManager->get('oro_customer.confirmation_required');
    }

    /**
     * @param CustomerUser $user
     */
    public function confirmRegistration(CustomerUser $user): void
    {
        $user->setConfirmed(true)
            ->setConfirmationToken($user->generateToken());

        try {
            $this->getEmailProcessor()->sendWelcomeNotification($user);
        } catch (\Swift_SwiftException $e) {
            $this->logger->error('Unable to send welcome notification email', ['exception' => $e]);
        }
    }

    /**
     * @param CustomerUser $user
     */
    public function confirmRegistrationByAdmin(CustomerUser $user): void
    {
        $user->setConfirmed(true);

        $this->sendWelcomeRegisteredByAdminEmail($user);
    }

    /**
     * @param CustomerUser $user
     */
    public function sendWelcomeRegisteredByAdminEmail(CustomerUser $user): void
    {
        $user->setConfirmationToken($user->generateToken());

        try {
            $this->getEmailProcessor()->sendWelcomeForRegisteredByAdminNotification($user);
        } catch (\Swift_SwiftException $e) {
            $this->logger->error(
                'Unable to send welcome notification email for registered by admin',
                ['exception' => $e]
            );
        }
    }

    /**
     * @param CustomerUser $user
     */
    public function sendConfirmationEmail(CustomerUser $user): void
    {
        $user->setConfirmed(false)
            ->setConfirmationToken($user->generateToken());

        $this->updateUser($user);

        try {
            $this->getEmailProcessor()->sendConfirmationEmail($user);
        } catch (\Swift_SwiftException $e) {
            $this->logger->error('Unable to send confirmation email', ['exception' => $e]);
        }
    }

    /**
     * @param CustomerUser $user
     */
    public function sendResetPasswordEmail(CustomerUser $user): void
    {
        $user->setConfirmationToken($user->generateToken());
        $this->getEmailProcessor()->sendResetPasswordEmail($user);
        $user->setPasswordRequestedAt(new \DateTime('now', new \DateTimeZone('UTC')));
    }

    /**
     * {@inheritdoc}
     */
    public function findUserBy(array $criteria): ?UserInterface
    {
        return parent::findUserBy(array_merge($criteria, ['isGuest' => false]));
    }

    /**
     * @return Processor
     */
    private function getEmailProcessor(): Processor
    {
        return $this->emailProcessorLink->getService();
    }
}
