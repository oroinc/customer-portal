<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Mailer\Processor;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\UserBundle\Entity\BaseUserManager;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * Provides a set of methods to simplify manage of the CustomerUser entity.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerUserManager extends BaseUserManager implements ContainerAwareInterface, LoggerAwareInterface
{
    /**
     * @varConfigManager
     */
    protected $configManager;

    /**
     * @var Processor
     */
    protected $emailProcessor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var FrontendHelper
     */
    private $frontendHelper;

    /**
     * @var LocalizationHelper
     */
    private $localizationHelper;

    /**
     * @var WebsiteManager
     */
    private $websiteManager;

    /**
     * @param string $class,
     * @param ManagerRegistry $registry,
     * @param EncoderFactoryInterface $encoderFactory,
     * @param FrontendHelper $frontendHelper
     * @param LocalizationHelper $localizationHelper
     * @param WebsiteManager $websiteManager
     */
    public function __construct(
        string $class,
        ManagerRegistry $registry,
        EncoderFactoryInterface $encoderFactory,
        FrontendHelper $frontendHelper,
        LocalizationHelper $localizationHelper,
        WebsiteManager $websiteManager
    ) {
        parent::__construct($class, $registry, $encoderFactory);

        $this->localizationHelper = $localizationHelper;
        $this->websiteManager = $websiteManager;
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param CustomerUser $user
     */
    public function register(CustomerUser $user)
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            $settings = $user->getWebsiteSettings($this->websiteManager->getCurrentWebsite())
                ?? new CustomerUserSettings($this->websiteManager->getCurrentWebsite());

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
     * @param CustomerUser $user
     */
    public function confirmRegistration(CustomerUser $user)
    {
        $user->setConfirmed(true)
            ->setConfirmationToken(null);
        $this->sendWelcomeEmail($user);
    }

    /**
     * @param CustomerUser $user
     */
    public function confirmRegistrationByAdmin(CustomerUser $user)
    {
        $user->setConfirmed(true)
            ->setConfirmationToken(null);
        $this->sendWelcomeRegisteredByAdminEmail($user);
    }

    /**
     * @param CustomerUser $user
     */
    public function sendWelcomeEmail(CustomerUser $user)
    {
        $user->setConfirmationToken($user->generateToken());

        try {
            $this->getEmailProcessor()->sendWelcomeNotification($user);
        } catch (\Swift_SwiftException $exception) {
            if (null !== $this->logger) {
                $this->logger->error('Unable to send welcome notification email', ['exception' => $exception]);
            }
        }
    }

    /**
     * @param CustomerUser $user
     */
    public function sendWelcomeRegisteredByAdminEmail(CustomerUser $user)
    {
        $user->setConfirmationToken($user->generateToken());

        try {
            $this->getEmailProcessor()->sendWelcomeForRegisteredByAdminNotification($user);
        } catch (\Swift_SwiftException $exception) {
            if (null !== $this->logger) {
                $this->logger->error(
                    'Unable to send welcome notification email for registered by admin',
                    [
                        'exception' => $exception
                    ]
                );
            }
        }
    }

    /**
     * @param CustomerUser $user
     */
    public function sendConfirmationEmail(CustomerUser $user)
    {
        $user->setConfirmed(false)
            ->setConfirmationToken($user->generateToken());
        try {
            $this->getEmailProcessor()->sendConfirmationEmail($user);
        } catch (\Swift_SwiftException $exception) {
            if (null !== $this->logger) {
                $this->logger->error('Unable to send confirmation email', ['exception' => $exception]);
            }
        }
    }

    /**
     * @param CustomerUser $user
     */
    public function sendResetPasswordEmail(CustomerUser $user)
    {
        $this->getEmailProcessor()->sendResetPasswordEmail($user);
    }

    /**
     * @param int $maxLength
     * @return string
     */
    public function generatePassword($maxLength)
    {
        $upperCase = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 1); // get 1 upper case letter
        $number = substr(str_shuffle('1234567890'), 0, 1); // get 1 digit
        $randomString = substr($upperCase . $number . $this->generateToken(), 0, $maxLength); // construct a password

        return str_shuffle($randomString);
    }

    /**
     * @param string $name
     * @return array|string
     */
    protected function getConfigValue($name)
    {
        if (!$this->configManager) {
            $this->configManager = $this->container->get('oro_config.manager');
        }

        return $this->configManager->get($name);
    }

    /**
     * @return Processor
     */
    protected function getEmailProcessor()
    {
        if (!$this->emailProcessor) {
            $this->emailProcessor = $this->container->get('oro_customer.mailer.processor');
        }

        return $this->emailProcessor;
    }

    /**
     * @return string
     */
    protected function generateToken()
    {
        return rtrim(strtr(base64_encode(hash('sha256', uniqid(mt_rand(), true), true)), '+/', '-_'), '=');
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return bool
     */
    public function isConfirmationRequired()
    {
        return (bool)$this->getConfigValue('oro_customer.confirmation_required');
    }

    /**
     * @param UserInterface $user
     */
    protected function assertRoles(UserInterface $user)
    {
        if ($user->isEnabled() && !$user->getRoles()) {
            throw new \RuntimeException('Enabled customer has not default role');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByUsername($username)
    {
        // Username and email for customer users are equal.
        // So, search can be performed by email field as well as by username field.
        return $this->findUserByEmail($username);
    }

    /**
     * {@inheritdoc}
     */
    public function findUserBy(array $criteria)
    {
        return parent::findUserBy(array_merge($criteria, ['isGuest' => false]));
    }

    /**
     * {@inheritdoc}
     */
    protected function isCaseInsensitiveEmailAddressesEnabled(): bool
    {
        return (bool) $this->getConfigValue('oro_customer.case_insensitive_email_addresses_enabled');
    }
}
