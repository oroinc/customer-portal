<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Mailer\Processor;
use Oro\Bundle\UserBundle\Entity\BaseUserManager;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CustomerUserManager extends BaseUserManager implements ContainerAwareInterface, LoggerAwareInterface
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var Processor
     */
    protected $emailProcessor;

    /** @var LoggerInterface */
    protected $logger;
    /**
     * @var ContainerInterface
     */
    protected $container;

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
    public function sendWelcomeEmail(CustomerUser $user)
    {
        try {
            $this->getEmailProcessor()->sendWelcomeNotification(
                $user,
                $this->isSendPasswordInWelcomeEmail() ? $user->getPlainPassword() : null
            );
        } catch (\Swift_SwiftException $exception) {
            if (null !== $this->logger) {
                $this->logger->error('Unable to send welcome notification email', ['exception' => $exception]);
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
     * @return bool
     */
    protected function isSendPasswordInWelcomeEmail()
    {
        return (bool)$this->getConfigValue('oro_customer.send_password_in_welcome_email');
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
    public function findUserBy(array $criteria)
    {
        $criteria = array_merge($criteria, ['isGuest' => false]);

        return $this->getRepository()->findOneBy($criteria);
    }
}
