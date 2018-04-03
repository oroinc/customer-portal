<?php

namespace Oro\Bundle\CustomerBundle\Mailer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Event\CustomerUserEmailSendEvent;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\EmailBundle\Tools\EmailHolderHelper;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Handles CustomerUser email sending logic
 */
class Processor extends CustomerUserProcessor
{
    const WELCOME_EMAIL_TEMPLATE_NAME = 'customer_user_welcome_email';
    const CONFIRMATION_EMAIL_TEMPLATE_NAME = 'customer_user_confirmation_email';
    const RESET_PASSWORD_EMAIL_TEMPLATE_NAME = 'customer_user_reset_password';

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * {@inheritdoc}
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        ConfigManager $configManager,
        EmailRenderer $renderer,
        EmailHolderHelper $emailHolderHelper,
        \Swift_Mailer $mailer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->managerRegistry   = $managerRegistry;
        $this->configManager     = $configManager;
        $this->renderer          = $renderer;
        $this->emailHolderHelper = $emailHolderHelper;
        $this->mailer            = $mailer;
        $this->eventDispatcher   = $eventDispatcher;

        parent::__construct($managerRegistry, $configManager, $renderer, $emailHolderHelper, $mailer);
    }

    /**
     * @param CustomerUser $customerUser
     * @param string $password
     * @return int
     */
    public function sendWelcomeNotification(CustomerUser $customerUser, $password)
    {
        return $this->getEmailTemplateAndSendEmail(
            $customerUser,
            static::WELCOME_EMAIL_TEMPLATE_NAME,
            ['entity' => $customerUser, 'password' => $password]
        );
    }

    /**
     * @param CustomerUser $customerUser
     * @return int
     */
    public function sendConfirmationEmail(CustomerUser $customerUser)
    {
        return $this->getEmailTemplateAndSendEmail(
            $customerUser,
            static::CONFIRMATION_EMAIL_TEMPLATE_NAME,
            ['entity' => $customerUser, 'token' => $customerUser->getConfirmationToken()]
        );
    }

    /**
     * @param CustomerUser $customerUser
     * @return int
     */
    public function sendResetPasswordEmail(CustomerUser $customerUser)
    {
        return $this->getEmailTemplateAndSendEmail(
            $customerUser,
            static::RESET_PASSWORD_EMAIL_TEMPLATE_NAME,
            ['entity' => $customerUser]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailTemplateAndSendEmail(
        UserInterface $user,
        $emailTemplateName,
        array $emailTemplateParams = []
    ) {
        $event = new CustomerUserEmailSendEvent($user, $emailTemplateName, $emailTemplateParams);
        $this->eventDispatcher->dispatch(CustomerUserEmailSendEvent::NAME, $event);

        return parent::getEmailTemplateAndSendEmail(
            $user,
            $event->getEmailTemplate(),
            $event->getEmailTemplateParams()
        );
    }
}
