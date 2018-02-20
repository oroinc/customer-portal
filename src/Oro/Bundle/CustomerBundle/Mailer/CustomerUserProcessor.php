<?php

namespace Oro\Bundle\CustomerBundle\Mailer;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Oro\Bundle\UserBundle\Mailer\BaseProcessor;

class CustomerUserProcessor extends BaseProcessor
{
    /**
     * @param UserInterface|CustomerUser $user
     * @return string
     */
    protected function getSenderEmail(UserInterface $user)
    {
        return $this->configManager->get(
            'oro_notification.email_notification_sender_email',
            false,
            false,
            $user->getWebsite()
        );
    }

    /**
     * @param UserInterface|CustomerUser $user
     * @return string
     */
    protected function getSenderName(UserInterface $user)
    {
        return $this->configManager->get(
            'oro_notification.email_notification_sender_name',
            false,
            false,
            $user->getWebsite()
        );
    }
}
